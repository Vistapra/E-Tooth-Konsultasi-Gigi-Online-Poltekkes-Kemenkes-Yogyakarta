<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MessageItem extends Component
{
    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function render()
    {
        return view('components.message-item');
    }
}