<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Certificate;

class CertificateBuilder extends Component
{
    public $title;
    public $html_content;
    public $json_content;
    public ?int $organization_id = null;

    public function mount()
    {
        $this->organization_id = auth()->user()->organization_id;
    }

    public function saveCertificate()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'html_content' => 'required|string',
        ]);

        Certificate::create([
            'title' => $this->title,
            'html_content' => $this->html_content,
            'json_content' => $this->json_content,
            'organization_id' => $this->organization_id
        ]);

        session()->flash('success', 'Certificate saved!');
        $this->reset(['title', 'html_content', 'json_content']);
    }

    public function render()
    {
        return view('livewire.certificate-builder');
    }
}

