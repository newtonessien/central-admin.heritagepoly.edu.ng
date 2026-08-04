<?php

namespace App\Services\JambDataImport;

class NameParserService
{
    public function parse(?string $fullName): array
    {
        $fullName = $this->normalize(
            (string) $fullName
        );

        if ($fullName === '') {

            return [
                'last_name' => null,
                'first_name' => null,
                'other_names' => null,
            ];
        }

        $parts = preg_split(
            '/\s+/',
            $fullName
        );

        $parts = array_values(
            array_filter($parts)
        );

           // Apply ucfirst to each part
        $parts = array_map(function($part) {
            return ucfirst(strtolower($part));
        }, $parts);

        $count = count($parts);

        /**
         * Single Name
         */
        if ($count === 1) {

            return [
                'last_name' => $parts[0],
                'first_name' => null,
                'other_names' => null,
            ];
        }

        /**
         * Two Names
         */
        if ($count === 2) {

            return [
                'last_name' => $parts[0],
                'first_name' => $parts[1],
                'other_names' => null,
            ];
        }

        /**
         * Three or More Names
         */
        return [

            'last_name' => $parts[0],

            'first_name' => $parts[1],

            'other_names' => implode(
                ' ',
                array_slice($parts, 2)
            ),
        ];
    }

    protected function normalize(
        string $name
    ): string {

        $name = trim($name);

        $name = preg_replace(
            '/\s+/',
            ' ',
            $name
        );

        return $name;
    }
}
