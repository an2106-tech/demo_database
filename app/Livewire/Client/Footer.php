<?php

namespace App\Livewire\Client;

use Livewire\Component;

class Footer extends Component
{
    public string $type = 'candidate';

    public function mount(string $type = 'candidate'): void
    {
        $this->type = in_array($type, ['candidate', 'employer'], true) ? $type : 'candidate';
    }

    public function render()
    {
        return view('livewire.client.footer', [
            'type' => $this->type,
            'isEmployerFooter' => $this->type === 'employer',
        ]);
    }
}
