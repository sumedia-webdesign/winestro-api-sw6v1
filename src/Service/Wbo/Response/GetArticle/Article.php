<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Response\GetArticle;

class Article
{
    protected $articleNumber = '';
    protected $articleNumberFormat = '';
    protected $productNumber = '';
    protected $name = '';
    protected $description = '';
    protected $type = '';
    protected $typeId = 0;
    protected $color = '';
    protected $country = '';
    protected $region = '';
    protected $stockWarning = 0;
    protected $weight = 0.0;
    protected $price = 0.0;
    protected $taxPercent = 0.0;
    protected $freeShipping = false;
    protected $litre = 0.0;
    protected $litrePrice = 0.0;
    protected $noLitrePrice = false;
    protected $notice = '';
    protected $shopnotice = '';
    protected $group = '';
    protected $kiloprice = 0.0;
    protected $fillingWeight = 0.0;
    protected $unitId = 0;
    protected $unit = 'Liter';
    protected $unitQuantity = 0;
    protected $ean = '';
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

    protected $isWine = false;
    protected $wareGroups = [];
    protected $bottles = 0.0;
    protected $articleType = '';
    protected $manufacturer = '';
    protected $category = '';

    /** @var WineDetails */
    protected $wineDetails;

    /** @var ArticleImages */
    protected $articleImages;

    /** helping properties */
    protected $stock = 9;
    protected $stockDate = '';

    protected $bundle = [];

    public function __construct(array $articleData)
    {
        $map = [
            ['articleNumber', 'artikel_nr'],
            ['name', 'artikel_name'],
            ['description', 'artikel_beschreibung'],
            ['type', 'artikel_typ'],
            ['typeId', 'artikel_typ_id'],
            ['color', 'artikel_farbe'],
            ['country', 'artikel_land'],
            ['region', 'artikel_region'],
            ['stockWarning', 'artickel_bestand_warnung_ab'],
            ['weight', 'artikel_gewicht'],
            ['price', 'artikel_preis'],
            ['taxPercent', 'artikel_mwst'],
            ['freeShipping', 'artikel_versandfrei'],
            ['litre', 'artikel_liter'],
            ['litrePrice', 'artikel_literpreis'],
            ['noLitrePrice', 'artikel_keinliterpreis'],
            ['group', 'artikel_typ'],
            ['notice', 'artikel_notiz'],
            ['shopnotice', 'artikel_shopnotiz'],
            ['kiloprice', 'artikel_kilopreis'],
            ['fillingWeight', 'artikel_fuellgewicht'],
            ['unitId', 'artikel_verpackung'],
            ['unit', 'artikel_verpackung_bezeichnung'],
            ['unitQuantity', 'artikel_verpackung_inhalt'],
            ['ean', 'artikel_ean13'],
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

            ['bottles', 'artikel_versandzahl'],
            ['articleType', 'artikel_typ'],
            ['manufacturer', 'artikel_erzeuger_name'],
            ['category', 'artikel_kategorie'],
            ['bundle', 'artikel_sort_anzahl']
        ];

        $articleData['artikel_nr'] = urldecode($articleData['artikel_nr']);

        $articleData['artikel_name'] = is_array($articleData['artikel_name'])
            ? implode(", ", $articleData['artikel_name']) : $articleData['artikel_name'];
        $articleData['artikel_name'] = htmlspecialchars_decode($articleData['artikel_name']);
        $articleData['artikel_beschreibung'] = is_array($articleData['artikel_beschreibung'])
            ? implode("\n", $articleData['artikel_beschreibung'])
            : $articleData['artikel_beschreibung'];
        $articleData['artikel_notiz'] = is_array($articleData['artikel_notiz'] ?? false)
            ? implode("\n", $articleData['artikel_notiz'])
            : $articleData['artikel_notiz'] ?? '';
        $articleData['artikel_shopnotiz'] = is_array($articleData['artikel_shopnotiz'])
            ? implode("\n", $articleData['artikel_shopnotiz'])
            : $articleData['artikel_shopnotiz'];
        $articleData['artikel_shopnotiz'] = html_entity_decode($articleData['artikel_shopnotiz']);

        $articleData['artikel_fuellgewicht'] = $articleData['artikel_fuellgewicht'] / 1000;

        if (isset($articleData['artikel_warengruppen'])) {
            $articleData['artikel_warengruppen']['warengruppe'] = is_string($articleData['artikel_warengruppen']['warengruppe'])
                ? [$articleData['artikel_warengruppen']['warengruppe']] : $articleData['artikel_warengruppen']['warengruppe'];
            if (!is_array($articleData['artikel_warengruppen']['warengruppe'])) {
                $articleData['artikel_warengruppen']['warengruppe'] = array();
            }
            foreach ($articleData['artikel_warengruppen']['warengruppe'] as $index => $warengruppe) {
                $articleData['artikel_warengruppen']['warengruppe'][$index] = htmlspecialchars_decode($warengruppe);
            }
            $this->setWareGroups($articleData['artikel_warengruppen']['warengruppe']);
        }

        foreach ($map as $mapData) {
            list($method, $dataKey) = $mapData;
            if (isset($articleData[$dataKey])) {
                $value = $this->cast($articleData[$dataKey], gettype($this->$method));
                $this->{'set' . ucfirst($method)}($value);
            }
        }

        if (1 == $articleData['artikel_sulfite'] && empty($articleData['artikel_sort_anzahl'])) {
            $this->setIsWine(true);
        }

        $this->createWineDetails($articleData);
        $this->createArticleImages($articleData);
    }

    public function getArticleNumber(): string
    {
        return $this->articleNumber;
    }

    public function setArticleNumber(string $articleNumber): void
    {
        $this->articleNumber = $articleNumber;
    }

    public function getArticleNumberFormat(): string
    {
        return $this->articleNumberFormat;
    }

    public function setArticleNumberFormat(string $articleNumberFormat): void
    {
        $this->articleNumberFormat = $articleNumberFormat;
    }

    public function getProductNumber(): string
    {
        return $this->productNumber;
    }

    public function setProductNumber(string $productNumber): void
    {
        $this->productNumber = $productNumber;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getTypeId(): int
    {
        return $this->typeId;
    }

    public function setTypeId(int $typeId): void
    {
        $this->typeId = $typeId;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function setRegion(string $region): void
    {
        $this->region = $region;
    }

    public function getStockWarning(): int
    {
        return $this->stockWarning;
    }

    public function setStockWarning(int $stockWarning): void
    {
        $this->stockWarning = $stockWarning;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): void
    {
        $this->weight = $weight;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getTaxPercent(): float
    {
        return $this->taxPercent;
    }

    public function setTaxPercent(float $taxPercent): void
    {
        $this->taxPercent = $taxPercent;
    }

    public function isFreeShipping(): bool
    {
        return $this->freeShipping;
    }

    public function setFreeShipping(bool $freeShipping): void
    {
        $this->freeShipping = $freeShipping;
    }

    public function getLitre(): float
    {
        return $this->litre;
    }

    public function setLitre(float $litre): void
    {
        $this->litre = $litre;
    }

    public function getLitrePrice(): float
    {
        return $this->litrePrice;
    }

    public function setLitrePrice(float $litrePrice): void
    {
        $this->litrePrice = $litrePrice;
    }

    public function isNoLitrePrice(): bool
    {
        return $this->noLitrePrice;
    }

    public function setNoLitrePrice(bool $noLitrePrice): void
    {
        $this->noLitrePrice = $noLitrePrice;
    }

    public function getNotice(): string
    {
        return $this->notice;
    }

    public function setNotice(string $notice): void
    {
        $this->notice = $notice;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    public function setIsWine(bool $is): void
    {
        $this->isWine = $is;
    }

    public function isWine(): bool
    {
        return $this->isWine;
    }

    public function getShopnotice(): string
    {
        return $this->shopnotice;
    }

    public function setShopnotice(string $shopnotice): void
    {
        $this->shopnotice = $shopnotice;
    }

    public function getKiloprice(): float
    {
        return $this->kiloprice;
    }

    public function setKiloprice(float $kiloprice): void
    {
        $this->kiloprice = $kiloprice;
    }

    public function getFillingWeight(): float
    {
        return $this->fillingWeight;
    }

    public function setFillingWeight(float $fillingWeight): void
    {
        $this->fillingWeight = $fillingWeight;
    }

    public function getWareGroups(): array
    {
        return $this->wareGroups;
    }

    public function setWareGroups(array $wareGroups): void
    {
        $this->wareGroups = $wareGroups;
    }

    public function getBottles(): float
    {
        return $this->bottles;
    }

    public function setBottles(float $bottles): void
    {
        $this->bottles = $bottles;
    }

    public function setArticleType(string $type): void
    {
        $this->articleType = $type;
    }

    public function getArticleType(): string
    {
        return $this->articleType;
    }

    public function setManufacturer(string $manufacturer): void
    {
        $this->manufacturer = $manufacturer;
    }

    public function getManufacturer(): string
    {
        return $this->manufacturer;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getUnitId(): int
    {
        return $this->unitId;
    }

    public function setUnitId(int $unitId): void
    {
        $this->unitId = $unitId;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): void
    {
        $this->unit = $unit;
    }

    public function getUnitQuantity(): int
    {
        return $this->unitQuantity;
    }

    public function setUnitQuantity(int $unitQuantity): void
    {
        $this->unitQuantity = $unitQuantity;
    }

    public function getEan(): string
    {
        return $this->ean;
    }

    public function setEan(string $ean): void
    {
        $this->ean = $ean;
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

    public function setWineDetails(WineDetails $wineDetails): void
    {
        $this->wineDetails = $wineDetails;
    }

    public function getWineDetails(): ?WineDetails
    {
        return $this->wineDetails;
    }

    public function setArticleImages(ArticleImages $articleImages): void
    {
        $this->articleImages = $articleImages;
    }

    public function getArticleImages(): ArticleImages
    {
        return $this->articleImages;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function getStockDate(): ?string
    {
        return $this->stockDate;
    }

    public function setStockDate(string $date): void
    {
        $this->stockDate = $date;
    }

    public function getBundle(): ?array
    {
        return $this->bundle;
    }

    public function setBundle(array $bundle): void
    {
        $this->bundle = $bundle;
    }

    protected function createWineDetails(array $articleData): void
    {
        $this->setWineDetails(new WineDetails($articleData));
    }

    protected function createArticleImages(array $articleData): void
    {
        $this->setArticleImages(new ArticleImages($articleData));
    }

    private function cast($var, string $type)
    {
        if (is_array($var) && ($type === 'string' || $type === 'boolean')) {
            $var = implode(', ', $var);
        }
        if ($type === 'double') {
            if (is_array($var)) {
                $var = implode(',', $var);
            }
            $var = str_replace(',', '.', (string) $var);
        }
        if (is_array($var) && $type === 'array') {
            return $var;
        }
        return eval('return (' . $type . ') "$var";');
    }
}
