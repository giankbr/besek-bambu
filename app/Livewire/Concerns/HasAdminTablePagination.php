<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\Url;

trait HasAdminTablePagination
{
    #[Url(as: 'per_page', except: 10)]
    public int $perPage = 10;

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, $this->perPageOptions(), true)) {
            $this->perPage = 10;
        }

        $this->resetPage();

        if (property_exists($this, 'selected') && is_array($this->selected)) {
            $this->selected = [];
        }
    }

    /**
     * @return list<int>
     */
    public function perPageOptions(): array
    {
        return [10, 25, 50, 100];
    }
}
