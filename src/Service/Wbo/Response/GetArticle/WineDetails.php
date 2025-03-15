<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Response\GetArticle;

class WineDetails
{
    protected $apnr = '';
    protected $year = '';
    protected $kind = '';
    protected $quality = '';
    protected $taste = '';
    protected $nuances = [];
    protected $awards = [];
    protected $cultivation = '';
    protected $location = '';
    protected $development = '';
    protected $grounds = '';
    protected $hasSulfite = true;
    protected $allergens = [];
    protected $sugar = 0.0;
    protected $acid = 0.0;
    protected $alcohol = 0.0;
    protected $protein = 0.0;
    protected $caloricValue = 0.0;
    protected $expertise = '';
    protected $isDrunken = false;
    protected $drinkingTemperature = '';
    protected $isStorable = '';
    protected $eLabelLink = '';
    protected $eLabelExtern = '';
    protected $bestBeforeDate = '';
    protected $fat = 0.0;
    protected $unsaturatedFats = 0.0;
    protected $carbonhydrates = 0.0;
    protected $salt = 0.0;

    protected $fiber = 0.0;
    protected $vitamins = '';
    protected $freeSulfitAcid = 0.0;
    protected $sulfitAcid = 0.0;
    protected $histamines = '';
    protected $glycerin = '';
    protected $labelText = '';

    public function __construct(array $articleData)
    {
        $map = [
            ['allergens', 'artikel_allergen'],
            ['apnr', 'artikel_apnr'],
            ['awards', 'artikel_auszeichnungen'],
            ['caloricValue', 'artikel_brennwert'],
            ['cultivation', 'artikel_anbaugebiet'],
            ['location', 'artikel_lage'],
            ['development', 'artikel_ausbau'],
            ['drinkingTemperature', 'artikel_trinktemperatur'],
            ['expertise', 'artikel_expertise'],
            ['grounds', 'artikel_boden'],
            ['hasSulfite', 'artikel_sulfite'],
            ['isDrunken', 'artikel_ausgetrunken'],
            ['isStorable', 'artikel_lagerfaehigkeit'],
            ['kind', 'artikel_sorte'],
            ['alcohol', 'artikel_alkohol'],
            ['acid', 'artikel_saeure'],
            ['nuances', 'artikel_nuancen'],
            ['protein', 'artikel_eiweiss'],
            ['quality', 'artikel_qualitaet'],
            ['sugar', 'artikel_zucker'],
            ['taste', 'artikel_geschmack'],
            ['year', 'artikel_jahrgang'],
            ['eLabelLink', 'artikel_labellink'],
            ['eLabelExtern', 'artikel_elabel_extern'],
            ['bestBeforeDate', 'artikel_mhd'],
            ['fat', 'artikel_fett'],
            ['unsaturatedFats', 'artikel_fetts'],
            ['carbonhydrates', 'artikel_kohlenhydrate'],
            ['salt', 'artikel_salz'],
            ['fiber', 'artikel_balast'],
            ['vitamins', 'artikel_vitamine'],
            ['freeSulfitAcid', 'artikel_frei_schwefelsaeure'],
            ['sulfitAcid', 'artikel_gesamt_schwefelsaeure'],
            ['histamines', 'artikel_histamin'],
            ['glycerin', 'artikel_glycerin'],
            ['labelText', 'artikel_labeltext'],
        ];

        $articleData['artikel_alkohol'] = str_replace('%', '', $articleData['artikel_alkohol']);

        foreach ($map as $mapData) {
            list($method, $dataKey) = $mapData;
            if (isset($articleData[$dataKey])) {
                $value = $this->cast($articleData[$dataKey], gettype($this->$method));
                $this->{'set' . ucfirst($method)}($value);
            }
        }
    }
    
    public function getApnr(): string
    {
        return $this->apnr;
    }

    public function setApnr(string $apnr): void
    {
        $this->apnr = $apnr;
    }

    public function getYear(): string
    {
        return $this->year;
    }

    public function setYear(string $year): void
    {
        $this->year = $year;
    }

    public function getKind(): string
    {
        return $this->kind;
    }

    public function setKind(string $kind): void
    {
        $this->kind = $kind;
    }

    public function getQuality(): string
    {
        return $this->quality;
    }

    public function setQuality(string $quality): void
    {
        $this->quality = $quality;
    }

    public function getTaste(): string
    {
        return $this->taste;
    }

    public function setTaste(string $taste): void
    {
        $this->taste = $taste;
    }

    public function getNuances(): array
    {
        return $this->nuances;
    }

    public function setNuances(array $nuances): void
    {
        $this->nuances = $nuances;
    }

    public function getAwards(): array
    {
        return $this->awards;
    }

    public function setAwards(array $awards): void
    {
        $this->awards = $awards;
    }

    public function getCultivation(): string
    {
        return $this->cultivation;
    }

    public function setCultivation(string $cultivation): void
    {
        $this->cultivation = $cultivation;
    }

    public function getGrounds(): string
    {
        return $this->grounds;
    }

    public function setGrounds(string $grounds): void
    {
        $this->grounds = $grounds;
    }

    public function hasSulfite(): bool
    {
        return $this->hasSulfite;
    }

    public function setHasSulfite(bool $hasSulfite): void
    {
        $this->hasSulfite = $hasSulfite;
    }

    public function getAllergens(): array
    {
        return $this->allergens;
    }

    public function setAllergens(array $allergens): void
    {
        $this->allergens = $allergens;
    }

    public function getSugar(): float
    {
        return $this->sugar;
    }

    public function setSugar(float $sugar): void
    {
        $this->sugar = $sugar;
    }

    public function getProtein(): float
    {
        return $this->protein;
    }

    public function setProtein(float $protein): void
    {
        $this->protein = $protein;
    }

    public function getCaloricValue(): float
    {
        return $this->caloricValue;
    }

    public function setCaloricValue(float $caloricValue): void
    {
        $this->caloricValue = $caloricValue;
    }

    public function getExpertise(): string
    {
        return $this->expertise;
    }

    public function setExpertise(string $expertise): void
    {
        $this->expertise = $expertise;
    }

    public function isDrunken(): bool
    {
        return $this->isDrunken;
    }

    public function setIsDrunken(bool $isDrunken): void
    {
        $this->isDrunken = $isDrunken;
    }

    public function getDrinkingTemperature(): string
    {
        return $this->drinkingTemperature;
    }

    public function setDrinkingTemperature(string $drinkingTemperature): void
    {
        $this->drinkingTemperature = $drinkingTemperature;
    }

    public function isStorable(): string
    {
        return $this->isStorable;
    }

    public function setIsStorable(string $isStorable): void
    {
        $this->isStorable = $isStorable;
    }

    public function getDevelopment(): string
    {
        return $this->development;
    }

    public function setDevelopment(string $development): void
    {
        $this->development = $development;
    }

    public function getAcid(): float
    {
        return $this->acid;
    }

    public function setAcid(float $acid): void
    {
        $this->acid = $acid;
    }

    public function getAlcohol(): float
    {
        return $this->alcohol;
    }

    public function setAlcohol(float $alcohol): void
    {
        $this->alcohol = $alcohol;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function getELabelLink(): string
    {
        return $this->eLabelLink;
    }

    public function setELabelLink(string $eLabelLink): void
    {
        $this->eLabelLink = $eLabelLink;
    }

    public function getELabelExtern(): string
    {
        return $this->eLabelExtern;
    }

    public function setELabelExtern(string $eLabelExtern): void
    {
        $this->eLabelExtern = $eLabelExtern;
    }

    public function getBestBeforeDate(): string
    {
        return $this->bestBeforeDate;
    }

    public function setBestBeforeDate(string $bestBeforeDate): void
    {
        $this->bestBeforeDate = $bestBeforeDate;
    }

    public function getFat(): float
    {
        return $this->fat;
    }

    public function setFat(float $fat): void
    {
        $this->fat = $fat;
    }

    public function getUnsaturatedFats(): float
    {
        return $this->unsaturatedFats;
    }

    public function setUnsaturatedFats(float $unsaturatedFats): void
    {
        $this->unsaturatedFats = $unsaturatedFats;
    }

    public function getCarbonhydrates(): float
    {
        return $this->carbonhydrates;
    }

    public function setCarbonhydrates(float $carbonhydrates): void
    {
        $this->carbonhydrates = $carbonhydrates;
    }

    public function getSalt(): float
    {
        return $this->salt;
    }

    public function setSalt(float $salt): void
    {
        $this->salt = $salt;
    }

    public function getFiber(): float
    {
        return $this->fiber;
    }

    public function setFiber(float $fiber): void
    {
        $this->fiber = $fiber;
    }

    public function getVitamins(): string
    {
        return $this->vitamins;
    }

    public function setVitamins(string $vitamins): void
    {
        $this->vitamins = $vitamins;
    }

    public function getFreeSulfitAcid(): float
    {
        return $this->freeSulfitAcid;
    }

    public function setFreeSulfitAcid(float $freeSulfitAcid): void
    {
        $this->freeSulfitAcid = $freeSulfitAcid;
    }

    public function getSulfitAcid(): float
    {
        return $this->sulfitAcid;
    }

    public function setSulfitAcid(float $sulfitAcid): void
    {
        $this->sulfitAcid = $sulfitAcid;
    }

    public function getHistamines(): string
    {
        return $this->histamines;
    }

    public function setHistamines(string $histamines): void
    {
        $this->histamines = $histamines;
    }

    public function getGlycerin(): string
    {
        return $this->glycerin;
    }

    public function setGlycerin(string $glycerin): void
    {
        $this->glycerin = $glycerin;
    }

    public function getLabelText(): string
    {
        return $this->labelText;
    }

    public function setLabelText(string $labelText): void
    {
        $this->labelText = $labelText;
    }

    private function cast($var, string $type)
    {
        if (is_array($var) && ($type === 'string' || $type === 'boolean' || $type === 'double')) {
            $var = implode(', ', $var);
        }
        if ($type === 'double') {
            $var = str_replace(',', '.', $var);
        }
        if (is_array($var) && $type === 'array') {
            return $var;
        }
        return eval('return (' . $type . ') "$var";');
    }
}
