<?php

namespace BaseRenderer;

use ExtendedException\ArrayExpected;
use ExtendedException\StringExpected;
use BaseClass\DataTypeService;
use BaseClass\PathService;
use BaseClass\StaticStringService;


class BaseRenderer
{

    const OPEN_DELIMETER = '{{';
    const CLOSE_DELIMETER = '}}';
    const OPEN_HTML_DELIMETER = '[[';
    const CLOSE_HTML_DELIMETER = ']]';

    public function render(array $input): string
    {

        $template = key($input);

        if (!is_string($template)) {
           throw new StringExpected([
               'input' => $template
           ]);
        }

        if (strpos($template, self::OPEN_DELIMETER) === false &&
            strpos($template, self::OPEN_HTML_DELIMETER) === false) {

            $fileName = $template;

            if (!file_exists($fileName)) {
                $fileName = PathService::getRootPath() . StaticStringService::addFirstSlash($template);
            }

            if (file_exists($fileName)) {
                $template = file_get_contents($fileName);
            }
        }

        $data = current($input);

        if (!is_array($data)) {
            throw new ArrayExpected([
                'input' => $input
            ]);
        }

        if (DataTypeService::isNumericArray($data)) {
            $a = [];
            foreach ($data as $value) {
                $a[] = $this->render([
                   $template => $value
                ]);
            }
            return \join('', $a);
        }


        $rawKeys = [];
        $htmlKeys = [];
        $rawValues = [];
        $htmlValues = [];
        foreach ($data as $key => $value) {

            if (DataTypeService::isAssociativeArray($value)) {
                $h = $this->render($value);
            } elseif (DataTypeService::isNumericArray($value)) {
                $a = [];
                foreach ($value as $record) {
                    $a[] = $this->render($record);
                }
                $h = \join('', $a);
            } else {
                $h = $value;
            }

            $htmlKeys[] = self::OPEN_HTML_DELIMETER . $key . self::CLOSE_HTML_DELIMETER;
            $rawKeys[] = self::OPEN_DELIMETER . $key . self::CLOSE_DELIMETER;
            $rawValues[] = $h;
            $htmlValues[] = htmlspecialchars($h);

        }

        $template = str_replace($htmlKeys, $htmlValues, $template);
        return str_replace($rawKeys, $rawValues, $template);

    }

}