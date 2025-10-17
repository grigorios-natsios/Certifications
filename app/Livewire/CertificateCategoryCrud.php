<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CertificateCategory;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class CertificateCategoryCrud extends Component
{
    use WithFileUploads;
    public $categories, $name, $svg, $category_id;
    public $showModal = false;

    public function render()
    {
        $this->categories = CertificateCategory::all();
        return view('livewire.certificate-category-crud');
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->svg = null;
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
            'svg'  => 'nullable|file|mimes:svg,xml|max:2048',
        ]);

        $path = null;
        if ($this->svg) {
            $path = $this->svg->store('certificates', 'public');
        }
        
        CertificateCategory::create([
            'name' => $this->name,
            'svg_path' => $path,
        ]);

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
        $this->validate([
            'name' => 'required|string|max:255',
            'svg'  => 'nullable|file|mimes:svg,xml|max:2048',
        ]);

        $category = CertificateCategory::find($this->category_id);
        if ($category) {
            $path = $category->svg_path;
            if ($this->svg) {
                $path = $this->svg->store('certificates', 'public');
            }

            $category->update([
                'name' => $this->name,
                'svg_path' => $path,
            ]);

            session()->flash('message', 'Category Updated Successfully.');
            $this->closeModal();
        }
    }

    public function delete($id)
    {
        $category = CertificateCategory::find($id);
        
        if (!$category) return;

        // Διαγραφή SVG αν υπάρχει
        if ($category->svg_path && \Storage::disk('public')->exists($category->svg_path)) {
            \Storage::disk('public')->delete($category->svg_path);
        }

        // Διαγραφή κατηγορίας
        $category->delete();

        session()->flash('message', 'Category and SVG deleted successfully.');
    }

}

