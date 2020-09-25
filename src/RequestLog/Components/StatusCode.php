<?php

namespace Nbj\RequestLog\Components;

use Illuminate\View\View;
use Illuminate\View\Component;
use Illuminate\Contracts\View\Factory;

class StatusCode extends Component
{
    /**
     * The HTML status code
     *
     * @var string $code
     */
    public $code;

    /**
     * Create a new component instance.
     *
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * Returns the appropriate boostrap color code, for a HTTP status code
     *
     * @return string
     */
    public function status()
    {
        $statuses = collect([
            "1" => "secondary",
            "2" => "success",
            "3" => "info",
            "4" => "warning",
            "5" => "danger",
        ]);

        $group = substr($this->code, 0, 1);

        return $statuses->get($group, "warning");
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Factory
     */
    public function render()
    {
        return view('request-logs::components.status-code');
    }
}
