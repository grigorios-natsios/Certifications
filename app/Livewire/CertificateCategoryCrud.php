<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CertificateCategory;

class CertificateCategoryCrud extends Component
{
    public $categories, $name, $category_id;
    public $showModal = false;

    public function render()
    {
        $this->categories = CertificateCategory::all();
        return view('livewire.certificate-category-crud');
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->category_id = null;
    }

    public function openModal()
    {
        $this->resetInputFields();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function store()
    {
        $this->validate(['name' => 'required|string|max:255']);
        CertificateCategory::create(['name' => $this->name]);
        session()->flash('message', 'Category Created Successfully.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $category = CertificateCategory::findOrFail($id);
        $this->category_id = $id;
        $this->name = $category->name;
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate(['name' => 'required|string|max:255']);
        if ($this->category_id) {
            CertificateCategory::find($this->category_id)->update(['name' => $this->name]);
            session()->flash('message', 'Category Updated Successfully.');
            $this->closeModal();
        }
    }

    public function delete($id)
    {
        CertificateCategory::find($id)->delete();
        session()->flash('message', 'Category Deleted Successfully.');
    }
}

