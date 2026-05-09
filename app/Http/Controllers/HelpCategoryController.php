<?php

namespace App\Http\Controllers;

use App\Http\Requests\HelpCategoryRequest;
use App\Models\HelpCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class HelpCategoryController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', HelpCategory::class);

        return view('help.categories.index', $this->viewData([
            'types' => HelpCategory::TYPES,
        ]));
    }

    public function datatable(Request $request)
    {
        Gate::authorize('viewAny', HelpCategory::class);

        $query = HelpCategory::query()->with('parent')->withCount('articles');
        $recordsTotal = (clone $query)->count();

        $this->applyFilters($query, $request);
        $this->applySearch($query, $request->input('search.value') ?: $request->input('keyword'));

        $recordsFiltered = (clone $query)->count();
        $this->applyOrdering($query, $request);

        $start = max((int) $request->input('start', 0), 0);
        $length = in_array((int) $request->input('length', 10), [10, 25, 50, 100], true) ? (int) $request->input('length', 10) : 10;

        $rows = $query->skip($start)->take($length)->get()->map(fn (HelpCategory $category, int $index) => [
            'DT_RowIndex' => $start + $index + 1,
            'name' => '<a href="'.route('help.categories.edit', $category).'">'.e($category->name).'</a>',
            'type' => e(str_replace('_', ' ', $category->type)),
            'parent' => e($category->parent?->name ?? '-'),
            'articles_count' => number_format($category->articles_count),
            'sort_no' => e($category->sort_no),
            'status' => view('help.partials.status', ['active' => $category->is_active])->render(),
            'actions' => View::make('help.categories.partials.actions', ['category' => $category])->render(),
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
        Gate::authorize('create', HelpCategory::class);

        return view('help.categories.form', $this->viewData($this->formOptions([
            'category' => new HelpCategory(['is_active' => true, 'sort_no' => 0]),
            'mode' => 'create',
        ])));
    }

    public function store(HelpCategoryRequest $request)
    {
        Gate::authorize('create', HelpCategory::class);

        HelpCategory::create($this->payload($request));

        return redirect()->route('help.categories.index')->with('status', 'Help category berhasil dibuat.');
    }

    public function show(HelpCategory $category)
    {
        return redirect()->route('help.categories.edit', $category);
    }

    public function edit(HelpCategory $category)
    {
        Gate::authorize('update', $category);

        return view('help.categories.form', $this->viewData($this->formOptions([
            'category' => $category,
            'mode' => 'edit',
        ])));
    }

    public function update(HelpCategoryRequest $request, HelpCategory $category)
    {
        Gate::authorize('update', $category);

        $category->update($this->payload($request));

        return redirect()->route('help.categories.index')->with('status', 'Help category berhasil diperbarui.');
    }

    public function destroy(HelpCategory $category)
    {
        Gate::authorize('delete', $category);

        abort_if($category->articles()->exists() || $category->children()->exists(), 422, 'Category masih memiliki article atau child category.');
        $category->delete();

        return redirect()->route('help.categories.index')->with('status', 'Help category berhasil dihapus.');
    }

    private function payload(HelpCategoryRequest $request): array
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_no'] = (int) ($data['sort_no'] ?? 0);

        return $data;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        foreach (['type', 'parent_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
    }

    private function applySearch(Builder $query, ?string $keyword): void
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return;
        }

        $query->where(fn (Builder $inner) => $inner
            ->where('name', 'like', '%'.$keyword.'%')
            ->orWhere('slug', 'like', '%'.$keyword.'%')
            ->orWhere('type', 'like', '%'.$keyword.'%'));
    }

    private function applyOrdering(Builder $query, Request $request): void
    {
        $columns = ['name', 'type', 'parent_id', 'articles_count', 'sort_no', 'is_active'];
        $order = collect($request->input('order', []))->first();
        $index = max(((int) ($order['column'] ?? 1)) - 1, 0);
        $direction = strtolower($order['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $query->orderBy($columns[$index] ?? 'sort_no', $direction)->orderBy('name');
    }

    private function formOptions(array $data = []): array
    {
        return array_merge([
            'types' => HelpCategory::TYPES,
            'parents' => HelpCategory::orderBy('type')->orderBy('sort_no')->orderBy('name')->get(['id', 'name', 'type']),
        ], $data);
    }

    private function viewData(array $data = []): array
    {
        return array_merge([
            'title' => 'Help Center',
            'subtitle' => 'Pusat dokumentasi aplikasi, user guide, developer docs, FAQ, dan troubleshooting internal.',
            'breadcrumbs' => [
                ['label' => 'Desk', 'url' => route('home')],
                ['label' => 'Help Center'],
                ['label' => 'Categories'],
            ],
        ], $data);
    }
}
