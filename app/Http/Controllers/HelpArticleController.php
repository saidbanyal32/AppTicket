<?php

namespace App\Http\Controllers;

use App\Http\Requests\HelpArticleRequest;
use App\Models\HelpArticle;
use App\Models\HelpArticleAttachment;
use App\Models\HelpCategory;
use App\Services\TicketService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class HelpArticleController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', HelpArticle::class);

        return view('help.articles.index', $this->viewData($this->formOptions([
            'summary' => $this->summary(),
        ])));
    }

    public function datatable(Request $request)
    {
        Gate::authorize('viewAny', HelpArticle::class);

        $query = HelpArticle::query()->with(['category', 'creator']);
        $recordsTotal = (clone $query)->count();

        $this->applyFilters($query, $request);
        $this->applySearch($query, $request->input('search.value') ?: $request->input('keyword'));

        $recordsFiltered = (clone $query)->count();
        $this->applyOrdering($query, $request);

        $start = max((int) $request->input('start', 0), 0);
        $length = in_array((int) $request->input('length', 10), [10, 25, 50, 100], true) ? (int) $request->input('length', 10) : 10;

        $rows = $query->skip($start)->take($length)->get()->map(fn (HelpArticle $article, int $index) => [
            'DT_RowIndex' => $start + $index + 1,
            'title' => '<a href="'.route('help.articles.show', $article).'">'.e($article->title).'</a>',
            'category' => e($article->category?->name ?? '-'),
            'article_type' => e(str_replace('_', ' ', $article->article_type)),
            'visibility' => e(str_replace('_', ' ', $article->visibility)),
            'status' => view('help.partials.publish-status', ['published' => $article->is_published])->render(),
            'updated_at' => e($article->updated_at?->format('Y-m-d H:i') ?? '-'),
            'actions' => View::make('help.articles.partials.actions', ['article' => $article])->render(),
        ]);

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    public function create()
    {
        Gate::authorize('create', HelpArticle::class);

        return view('help.articles.form', $this->viewData($this->formOptions([
            'article' => new HelpArticle(['article_type' => 'USER_GUIDE', 'visibility' => 'INTERNAL', 'is_published' => true]),
            'mode' => 'create',
        ])));
    }

    public function store(HelpArticleRequest $request, TicketService $ticketService)
    {
        Gate::authorize('create', HelpArticle::class);

        $data = $this->payload($request, $ticketService->currentUserId());
        $article = HelpArticle::create($data);
        $this->storeAttachments($article, $request->file('attachments', []), $ticketService->currentUserId());

        return redirect()->route('help.articles.show', $article)->with('status', 'Help article berhasil dibuat.');
    }

    public function show(HelpArticle $article)
    {
        Gate::authorize('view', $article);

        $article->load(['category.parent', 'attachments.uploader', 'creator', 'updater']);
        $related = HelpArticle::query()
            ->published()
            ->whereKeyNot($article->id)
            ->where(fn (Builder $query) => $query
                ->where('category_id', $article->category_id)
                ->orWhere('article_type', $article->article_type))
            ->latest('published_at')
            ->limit(5)
            ->get(['id', 'title', 'slug', 'article_type', 'category_id']);

        return view('help.articles.show', $this->viewData([
            'article' => $article,
            'categories' => $this->categoryTree(),
            'related' => $related,
        ]));
    }

    public function edit(HelpArticle $article)
    {
        Gate::authorize('update', $article);

        $article->load('attachments');

        return view('help.articles.form', $this->viewData($this->formOptions([
            'article' => $article,
            'mode' => 'edit',
        ])));
    }

    public function update(HelpArticleRequest $request, HelpArticle $article, TicketService $ticketService)
    {
        Gate::authorize('update', $article);

        $article->update($this->payload($request, $ticketService->currentUserId(), $article));
        $this->storeAttachments($article, $request->file('attachments', []), $ticketService->currentUserId());

        return redirect()->route('help.articles.show', $article)->with('status', 'Help article berhasil diperbarui.');
    }

    public function destroy(HelpArticle $article)
    {
        Gate::authorize('delete', $article);

        $article->delete();

        return redirect()->route('help.articles.index')->with('status', 'Help article berhasil dihapus.');
    }

    public function togglePublish(HelpArticle $article)
    {
        Gate::authorize('publish', $article);

        $article->forceFill([
            'is_published' => ! $article->is_published,
            'published_at' => $article->is_published ? null : now(),
            'updated_by' => app(TicketService::class)->currentUserId(),
        ])->save();

        return back()->with('status', $article->is_published ? 'Article berhasil dipublish.' : 'Article berhasil di-unpublish.');
    }

    public function destroyAttachment(HelpArticle $article, HelpArticleAttachment $attachment)
    {
        Gate::authorize('update', $article);
        abort_unless($attachment->article_id === $article->id, 404);

        Storage::disk($attachment->disk ?: 'public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('status', 'Attachment berhasil dihapus.');
    }

    private function payload(HelpArticleRequest $request, string $userId, ?HelpArticle $article = null): array
    {
        $data = $request->validated();
        $isPublished = $request->boolean('is_published');

        $payload = [
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => $data['slug'] ?: Str::slug($data['title']),
            'short_description' => $data['short_description'] ?? null,
            'content' => $data['content'],
            'article_type' => $data['article_type'],
            'visibility' => $data['visibility'],
            'tags' => $this->parseTags($data['tags'] ?? null),
            'is_published' => $isPublished,
            'published_at' => $isPublished ? ($article?->published_at ?: now()) : null,
            'updated_by' => $userId,
        ];

        if (! $article?->exists) {
            $payload['created_by'] = $userId;
        }

        if ($request->hasFile('thumbnail')) {
            if ($article?->thumbnail) {
                Storage::disk('public')->delete($article->thumbnail);
            }

            $payload['thumbnail'] = $request->file('thumbnail')->store('help/thumbnails', 'public');
        }

        return $payload;
    }

    private function parseTags(?string $tags): array
    {
        return str($tags ?: '')
            ->explode(',')
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function storeAttachments(HelpArticle $article, array $files, string $userId): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $extension = strtolower((string) $file->getClientOriginalExtension());
            $fileName = Str::uuid().($extension ? '.'.$extension : '');
            $path = $file->storeAs('help/articles/'.$article->id, $fileName, 'public');

            $article->attachments()->create([
                'original_name' => $file->getClientOriginalName(),
                'file_name' => $fileName,
                'file_extension' => $extension,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'disk' => 'public',
                'file_path' => $path,
                'uploaded_by' => $userId,
            ]);
        }
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        foreach (['category_id', 'article_type', 'visibility'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }
    }

    private function applySearch(Builder $query, ?string $keyword): void
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return;
        }

        $query->where(fn (Builder $inner) => $inner
            ->where('title', 'like', '%'.$keyword.'%')
            ->orWhere('content', 'like', '%'.$keyword.'%')
            ->orWhere('tags', 'like', '%'.$keyword.'%'));
    }

    private function applyOrdering(Builder $query, Request $request): void
    {
        $columns = ['title', 'category_id', 'article_type', 'visibility', 'is_published', 'updated_at'];
        $order = collect($request->input('order', []))->first();
        $index = max(((int) ($order['column'] ?? 6)) - 1, 0);
        $direction = strtolower($order['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($columns[$index] ?? 'updated_at', $direction);
    }

    private function formOptions(array $data = []): array
    {
        return array_merge([
            'categories' => HelpCategory::where('is_active', true)->orderBy('type')->orderBy('sort_no')->orderBy('name')->get(['id', 'name', 'type']),
            'articleTypes' => HelpArticle::ARTICLE_TYPES,
            'visibilities' => HelpArticle::VISIBILITIES,
        ], $data);
    }

    private function categoryTree()
    {
        return HelpCategory::query()
            ->where('is_active', true)
            ->withCount(['articles' => fn (Builder $query) => $query->published()])
            ->orderBy('type')
            ->orderBy('sort_no')
            ->orderBy('name')
            ->get();
    }

    private function summary(): array
    {
        return [
            'User Guide' => HelpArticle::where('article_type', 'USER_GUIDE')->count(),
            'Developer Docs' => HelpArticle::where('article_type', 'DEVELOPER_DOCS')->count(),
            'FAQ' => HelpArticle::where('article_type', 'FAQ')->count(),
            'Troubleshooting' => HelpArticle::where('article_type', 'TROUBLESHOOTING')->count(),
            'Published' => HelpArticle::where('is_published', true)->count(),
            'Draft' => HelpArticle::where('is_published', false)->count(),
        ];
    }

    private function viewData(array $data = []): array
    {
        return array_merge([
            'title' => 'Help Center',
            'subtitle' => 'Dokumentasi penggunaan aplikasi, teknis developer, setup, FAQ, dan troubleshooting.',
            'breadcrumbs' => [
                ['label' => 'Desk', 'url' => route('home')],
                ['label' => 'Help Center'],
                ['label' => 'Articles'],
            ],
        ], $data);
    }
}
