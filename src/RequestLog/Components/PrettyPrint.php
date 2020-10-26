<?php

namespace Cego\RequestLog\Components;

use Exception;
use Illuminate\View\View;
use Illuminate\View\Component;
use Illuminate\Contracts\View\Factory;

class PrettyPrint extends Component
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
     * @param object|array|string $data
     *
     * @throws \JsonException
     */
    public function __construct($data)
    {
        $this->json = $this->prettyPrint($data);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Factory|View
     */
    public function render()
    {
        return view('request-logs::components.pretty-print');
    }

    /**
     * @param object|array|string $data
     *
     * @return string
     */
    private function prettyPrint($data)
    {
        try {
            // Try to pretty print as json
            return $this->prettyPrintJson($data);
        } catch (Exception $jsonException) {
            // If invalid json then just display the data
            return $this->printArbitraryReadable($data);
        }
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
            try {
                $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            } catch (Exception $jsonException) {
                return $data;
            }
        }

        return json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    /**
     * @param object|array|string $data
     *
     * @return string
     */
    private function printArbitraryReadable($data)
    {
        // If it is a string, we then escape any html so it can be displayed as a string
        if (is_string($data)) {
            $data = htmlentities($data);
        }

        /** @var string $response */
        $response = print_r($data, true);

        return $response;
    }
}
