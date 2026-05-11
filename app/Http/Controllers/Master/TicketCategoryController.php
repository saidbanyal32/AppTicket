<?php

namespace App\Http\Controllers\Master;

use App\Models\Master\RefTicketCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

class TicketCategoryController extends BaseMasterController
{
    protected string $resourceKey = 'ticket-categories';

    public function index(): View
    {
        $this->authorizeMasterAction('view');

        $options = $this->selectOptions();
        $options['ticket-categories'] = $this->parentFilterOptions();

        return view('master-data.index', $this->viewData([
            'options' => $options,
            'datatableColumns' => $this->datatableColumns(),
            'filterFields' => $this->filterFields(),
        ]));
    }

    private function parentFilterOptions(): array
    {
        return RefTicketCategory::query()
            ->whereHas('children')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Model $category) => ['id' => $category->id, 'label' => $category->name])
            ->all();
    }
}
