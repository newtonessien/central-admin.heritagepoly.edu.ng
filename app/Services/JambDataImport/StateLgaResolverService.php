<?php

namespace App\Services\JambDataImport;

use App\Models\Admissions\Lga;
use App\Models\Admissions\State;

class StateLgaResolverService
{
    protected array $states = [];

    protected array $lgas = [];

    public function __construct()
    {
        $this->loadStates();
        $this->loadLgas();
    }

    protected function loadStates(): void
    {
        $this->states = State::query()
            ->get()
            ->keyBy(
                fn ($state)
                    => $this->normalize(
                        $state->name
                    )
            )
            ->all();
    }

    protected function loadLgas(): void
    {
        foreach (Lga::all() as $lga) {

            $this->lgas[
                $lga->state_id
            ][
                $this->normalize(
                    $lga->name
                )
            ] = $lga;
        }
    }

    protected function normalize(
        ?string $value
    ): string {

        if (!$value) {
            return '';
        }

        $value = strtoupper(trim($value));

        $value = str_replace(
            ['-', '/', '.', ',', "'", '’'],
            ' ',
            $value
        );

        $value = preg_replace(
            '/\s+/',
            ' ',
            $value
        );

        return trim($value);
    }

    protected function stateAlias(
        string $state
    ): string {

        return match ($state) {

            'NASSARAWA' => 'NASARAWA',

            'FCT ABUJA' => 'ABUJA FCT',

            default => $state,
        };
    }

    protected function lgaAlias(
        string $lga
    ): string {

        return match ($lga) {

            'IBINO IBOM' => 'IBIONO IBOM',

            'ETINAM' => 'ETINAN',

            'URVE OFFONG ORUKO'
                => 'URUE OFFONG ORUKO',

            'IDEMELI NORTH'
                => 'IDEMILI NORTH',

            'IDEMELI SOUTH'
                => 'IDEMILI SOUTH',

            'YENEGOA'
                => 'YENAGOA',

            'AKUKUTORU'
                => 'AKUKU TORU',

            'OKIRIKA'
                => 'OKRIKA',

            default => $lga,
        };
    }

    public function resolve(
        ?string $stateName,
        ?string $lgaName
    ): array {

        $stateId = null;
        $lgaId = null;

        if ($stateName) {

            $stateKey = $this->stateAlias(
                $this->normalize(
                    $stateName
                )
            );

            $state = $this->states[
                $stateKey
            ] ?? null;

            if ($state) {

                $stateId = $state->id;

                if (
                    $lgaName
                    && isset(
                        $this->lgas[$stateId]
                    )
                ) {

                    $lgaKey =
                        $this->lgaAlias(
                            $this->normalize(
                                $lgaName
                            )
                        );

                    $lga =
                        $this->lgas[
                            $stateId
                        ][
                            $lgaKey
                        ] ?? null;

                    if ($lga) {
                        $lgaId = $lga->id;
                    }
                }
            }
        }

        return [
            'state_id' => $stateId,
            'lga_id' => $lgaId,
        ];
    }
}
