<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CertificateCategory;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class CertificateCategoryCrud extends Component
{
    use WithFileUploads;
    public $categories, $name, $html_template, $category_id;
    public $showModal = false;

    public function render()
    {
        $this->categories = CertificateCategory::all();
        return view('livewire.certificate-category-crud');
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->html_template = null;
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
        $this->validate([
            'name' => 'required|string|max:255',
            'html_template' => 'required|string',
        ]);
        
        CertificateCategory::create([
            'name' => $this->name,
            'html_template' => $this->html_template,
        ]);

        session()->flash('message', 'Category Created Successfully.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $category = CertificateCategory::findOrFail($id);
        $this->category_id = $id;
        $this->name = $category->name;
        $this->html_template = $category->html_template;
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'html_template' => 'required|string',
        ]);

        $category = CertificateCategory::find($this->category_id);
        if ($category) {
            $category->update([
                'name' => $this->name,
                'html_template' => $this->html_template,
            ]);

            session()->flash('message', 'Category Updated Successfully.');
            $this->closeModal();
        }
    }

    public function delete($id)
    {
        $category = CertificateCategory::find($id);
        
        if (!$category) return;
        // Διαγραφή κατηγορίας
        $category->delete();

        session()->flash('message', 'Category deleted successfully.');
    }

}

