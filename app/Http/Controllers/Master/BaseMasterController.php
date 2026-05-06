<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\MasterDataRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\View;

abstract class BaseMasterController extends Controller
{
    protected string $resourceKey;

    public function index(): View
    {
        return view('master-data.index', $this->viewData([
            'options' => $this->selectOptions(),
            'datatableColumns' => $this->datatableColumns(),
            'filterFields' => $this->filterFields(),
        ]));
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = $this->query();
        $recordsTotal = (clone $query)->count();

        $this->applyAdvancedFilters($query, $request);
        $this->applySearch($query, $request->input('search.value') ?: $request->input('keyword'));

        $recordsFiltered = (clone $query)->count();

        $this->applyOrdering($query, $request);

        $start = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 10);
        $length = in_array($length, [10, 25, 50, 100], true) ? $length : 10;

        $records = $query->skip($start)->take($length)->get();

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records->values()->map(fn (Model $record, int $index) => $this->datatableRow($record, $start + $index + 1)),
        ]);
    }

    public function create(): View
    {
        return view('master-data.form', $this->viewData([
            'record' => $this->newModel(),
            'options' => $this->selectOptions(),
            'mode' => 'create',
        ]));
    }

    public function store(MasterDataRequest $request): RedirectResponse
    {
        $record = $this->newModel();
        $record->fill($request->validated());
        $record->save();

        return redirect()->route($this->config('route').'.show', $record)
            ->with('status', $this->config('title').' berhasil dibuat.');
    }

    public function show(Request $request, mixed $record): View
    {
        $record = $this->findRecord($record);
        $record->load($this->config('with') ?? []);

        return view('master-data.show', $this->viewData([
            'record' => $record,
            'options' => $this->selectOptions($record),
        ]));
    }

    public function edit(Request $request, mixed $record): View
    {
        $record = $this->findRecord($record);
        $record->load($this->config('with') ?? []);

        return view('master-data.form', $this->viewData([
            'record' => $record,
            'options' => $this->selectOptions($record),
            'mode' => 'edit',
        ]));
    }

    public function update(MasterDataRequest $request, mixed $record): RedirectResponse
    {
        $record = $this->findRecord($record);
        $record->fill($request->validated());
        $record->save();

        return redirect()->route($this->config('route').'.show', $record)
            ->with('status', $this->config('title').' berhasil diperbarui.');
    }

    public function destroy(Request $request, mixed $record): RedirectResponse
    {
        $record = $this->findRecord($record);
        $record->delete();

        return redirect()->route($this->config('route').'.index')
            ->with('status', $this->config('title').' berhasil dihapus.');
    }

    protected function config(?string $key = null): mixed
    {
        $config = config('master-data.'.$this->resourceKey);

        return $key ? ($config[$key] ?? null) : $config;
    }

    protected function query()
    {
        return $this->newModel()->newQuery()->with($this->config('with') ?? []);
    }

    protected function newModel(): Model
    {
        $class = $this->config('model');

        return new $class;
    }

    protected function findRecord(mixed $record): Model
    {
        if ($record instanceof Model) {
            return $record;
        }

        return $this->query()->whereKey($record)->firstOrFail();
    }

    protected function selectOptions(?Model $current = null): array
    {
        $options = [];

        foreach ($this->config('fields') as $field) {
            if (($field['type'] ?? null) !== 'select' || empty($field['relation'])) {
                continue;
            }

            $relationConfig = config('master-data.'.$field['relation']);
            $model = new $relationConfig['model'];
            $display = $relationConfig['display'] ?? 'name';

            $query = $model->newQuery()->orderBy($display);

            if ($current && $field['relation'] === $this->resourceKey && $current->exists) {
                $query->whereKeyNot($current->getKey());
            }

            $options[$field['relation']] = $query->get(['id', $display])
                ->map(fn (Model $item) => ['id' => $item->id, 'label' => $item->{$display}])
                ->all();
        }

        return $options;
    }

    protected function viewData(array $data = []): array
    {
        $config = $this->config();

        return array_merge([
            'config' => $config,
            'title' => $config['title'],
            'subtitle' => $config['subtitle'] ?? null,
            'breadcrumbs' => [
                ['label' => 'Desk', 'url' => route('home')],
                ['label' => $config['group'] ?? 'Master Data'],
                ['label' => $config['title']],
            ],
        ], $data);
    }

    protected function datatableColumns(): array
    {
        return collect($this->config('columns'))
            ->values()
            ->map(fn (array $column, int $index) => [
                'data' => 'column_'.$index,
                'name' => $column['key'],
                'label' => $column['label'],
                'orderable' => true,
                'searchable' => true,
            ])
            ->all();
    }

    protected function filterFields(): array
    {
        return collect($this->config('fields'))
            ->filter(fn (array $field) => ($field['type'] ?? null) === 'select' && ! empty($field['relation']))
            ->map(fn (array $field, string $key) => [
                'key' => $key,
                'label' => $field['label'],
                'relation' => $field['relation'],
            ])
            ->values()
            ->all();
    }

    protected function applyAdvancedFilters(Builder $query, Request $request): void
    {
        if ($request->filled('status') && $this->hasConfiguredColumn('is_active')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        foreach ($this->filterFields() as $filter) {
            if ($request->filled($filter['key'])) {
                $query->where($filter['key'], $request->input($filter['key']));
            }
        }
    }

    protected function applySearch(Builder $query, ?string $keyword): void
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return;
        }

        $query->where(function (Builder $inner) use ($keyword) {
            foreach ($this->config('columns') as $column) {
                $key = $column['key'];

                if (($column['type'] ?? null) === 'status') {
                    $normalized = strtolower($keyword);

                    if (str_contains('active', $normalized)) {
                        $inner->orWhere($key, true);
                    }

                    if (str_contains('inactive', $normalized) || str_contains('non aktif', $normalized)) {
                        $inner->orWhere($key, false);
                    }

                    continue;
                }

                if (str_contains($key, '.')) {
                    [$relation, $field] = explode('.', $key, 2);
                    $inner->orWhereHas($relation, fn (Builder $relationQuery) => $relationQuery->where($field, 'like', '%'.$keyword.'%'));

                    continue;
                }

                $inner->orWhere($key, 'like', '%'.$keyword.'%');
            }
        });
    }

    protected function applyOrdering(Builder $query, Request $request): void
    {
        $order = collect($request->input('order', []))->first();
        $columnIndex = (int) ($order['column'] ?? 1);
        $direction = strtolower($order['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $column = $request->input('columns.'.$columnIndex.'.name');

        if (! $column || $column === 'DT_RowIndex' || $column === 'actions') {
            $query->orderByDesc($this->newModel()->qualifyColumn('id'));

            return;
        }

        if (str_contains($column, '.')) {
            $this->orderByRelationColumn($query, $column, $direction);

            return;
        }

        $query->orderBy($this->newModel()->qualifyColumn($column), $direction);
    }

    protected function orderByRelationColumn(Builder $query, string $column, string $direction): void
    {
        [$relationName, $field] = explode('.', $column, 2);
        $model = $this->newModel();

        if (! method_exists($model, $relationName)) {
            $query->orderByDesc($model->qualifyColumn('id'));

            return;
        }

        $relation = $model->{$relationName}();

        if (! $relation instanceof BelongsTo) {
            $query->orderByDesc($model->qualifyColumn('id'));

            return;
        }

        $related = $relation->getRelated();

        $query->orderBy(
            $related->newQuery()
                ->select($related->qualifyColumn($field))
                ->whereColumn($related->qualifyColumn($relation->getOwnerKeyName()), $model->qualifyColumn($relation->getForeignKeyName()))
                ->limit(1),
            $direction
        );
    }

    protected function datatableRow(Model $record, int $number): array
    {
        $row = [
            'DT_RowIndex' => $number,
        ];

        foreach ($this->config('columns') as $index => $column) {
            $value = data_get($record, $column['key']);
            $row['column_'.$index] = $this->formatDatatableValue($record, $column, $value);
        }

        $row['actions'] = ViewFactory::make('partials.erp.datatable-actions', [
            'config' => $this->config(),
            'record' => $record,
        ])->render();

        return $row;
    }

    protected function formatDatatableValue(Model $record, array $column, mixed $value): string
    {
        if (($column['type'] ?? null) === 'status') {
            $label = $value ? 'Active' : 'Inactive';
            $class = $value ? 'success' : 'danger';

            return '<span class="erp-status '.$class.'">'.$label.'</span>';
        }

        if ($value instanceof Carbon) {
            return e($value->format('Y-m-d'));
        }

        return filled($value) ? e((string) $value) : '-';
    }

    protected function hasConfiguredColumn(string $key): bool
    {
        return collect($this->config('columns'))->contains(fn (array $column) => $column['key'] === $key);
    }
}
