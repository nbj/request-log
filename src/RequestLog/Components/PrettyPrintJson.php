<?php

namespace Cego\RequestLog\Components;

use Illuminate\View\View;
use Illuminate\View\Component;
use Illuminate\Contracts\View\Factory;

class PrettyPrintJson extends Component
{
    /**
     * Json string pretty printed
     *
     * @var string
     */
    public $json;

    /**
     * Create a new component instance.
     *
     * @param object|array|string $json
     *
     * @throws \JsonException
     */
    public function __construct($json)
    {
        $this->json = $this->prettyPrintJson($json);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Factory|View
     */
    public function render()
    {
        return view('request-logs::components.pretty-print-json');
    }

    /**
     * Takes either a json string or some data, and returns a json string that has been pretty printed.
     *
     * @param object|array|string $data
     *
     * @return string
     *
     * @throws \JsonException
     */
    private function prettyPrintJson($data)
    {
        if (is_string($data) && strtolower($data) === "null") {
            return "{}";
        }

        // If it is already in json format, we then need to turn it into an object, so it can be reformatted in pretty print
        if (is_string($data)) {
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        }

        return json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }
}
