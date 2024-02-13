<?php

declare(strict_types=1);

namespace Sumedia\Wbo\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class WboArticlesEntity extends Entity
{
    use EntityIdTrait;

    protected $articleNumber = '';

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

    protected $isFreeShipping = false;

    protected $noLitrePrice = false;

    protected $notice = '';

    protected $groupId = '';

    protected $isWine = false;

    protected $bottles = 0.0;

    protected $shopnotice = '';

    protected $kiloprice = 0.0;

    protected $fillingWeight = 0.0;

    protected $waregroup = '';

    protected $allegens = '';

    protected $apnr = '';

    protected $awards = '';

    protected $caloricValue = 0.0;

    protected $cultivation = '';

    protected $location = '';

    protected $development = '';

    protected $drinkingTemperature = '';

    protected $expertise = '';

    protected $grounds = '';

    protected $hasSulfite = false;

    protected $isDrunken = false;

    protected $isStorable = '';

    protected $kind = '';

    protected $alcohol = 0.0;

    protected $litre = 0.0;

    protected $litrePrice = 0.0;

    protected $nuances = '';

    protected $protein = 0.0;

    protected $quality = '';

    protected $sugar = 0.0;

    protected $taste = '';

    protected $year = '';

    protected $acid = 0.0;

    protected $image1 = '';

    protected $image2 = '';

    protected $image3 = '';

    protected $image4 = '';

    protected $bigImage1 = '';

    protected $bigImage2 = '';

    protected $bigImage3 = '';

    protected $bigImage4 = '';

    protected $category = '';

    protected $unitId = 0;

    protected $unit = '';

    protected $unitQuantity = 0;

    protected $ean = '';

    protected $manufacturer = '';

    protected $importedAt = '';

    protected $stock = 0;

    protected $stockDate = '3000-01-01 00:00:01';

    protected $bundle = [];

    public function getArticleNumber() : string
    {
        return $this->articleNumber ?: '';
    }

    public function setArticleNumber(string $articleNumber): void
    {
        $this->articleNumber = $articleNumber;
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
        return $this->name ?: '';
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description ?: '';
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
        return $this->typeId ?: 0;
    }

    public function setTypeId(int $typeId): void
    {
        $this->typeId = $typeId;
    }

    public function getColor(): string
    {
        return $this->color ?: '';
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getCountry(): string
    {
        return $this->country ?: '';
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getRegion(): string
    {
        return $this->region ?: '';
    }

    public function setRegion(string $region): void
    {
        $this->region = $region;
    }

    public function getStockWarning(): int
    {
        return $this->stockWarning ?: 0;
    }

    public function setStockWarning(int $stockWarning): void
    {
        $this->stockWarning = $stockWarning;
    }

    public function getWeight(): float
    {
        return $this->weight ?: 0.0;
    }

    public function setWeight(float $weight): void
    {
        $this->weight = $weight;
    }

    public function getPrice(): float
    {
        return $this->price ?: 0.0;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getTaxPercent(): float
    {
        return $this->taxPercent ?: 0.0;
    }

    public function setTaxPercent(float $taxPercent): void
    {
        $this->taxPercent = $taxPercent;
    }

    public function isFreeShipping(): bool
    {
        return $this->isFreeShipping ?: false;
    }

    public function setIsFreeShipping(bool $isFreeShipping): void
    {
        $this->isFreeShipping = $isFreeShipping;
    }

    public function isNoLitrePrice(): bool
    {
        return $this->noLitrePrice ?: false;
    }

    public function setNoLitrePrice(bool $noLitrePrice): void
    {
        $this->noLitrePrice = $noLitrePrice;
    }

    public function getNotice(): string
    {
        return $this->notice ?: '';
    }

    public function setNotice(string $notice): void
    {
        $this->notice = $notice;
    }

    public function getGroupId(): string
    {
        return $this->groupId ?: '';
    }

    public function setGroupId(string $groupId): void
    {
        $this->groupId = $groupId;
    }

    public function isWine(): bool
    {
        return $this->isWine ?: false;
    }

    public function setIsWine(bool $isWine): void
    {
        $this->isWine = $isWine;
    }

    public function getBottles(): float
    {
        return $this->bottles ?: 1.0;
    }

    public function setBottles(float $bottles): void
    {
        $this->bottles = $bottles;
    }

    public function getShopnotice(): string
    {
        return $this->shopnotice ?: '';
    }

    public function setShopnotice(string $shopnotice): void
    {
        $this->shopnotice = $shopnotice;
    }

    public function getKiloprice(): float
    {
        return $this->kiloprice ?: 0.0;
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

    public function getWaregroup(): string
    {
        return $this->waregroup ?: '';
    }

    public function setWaregroup(string $waregroup): void
    {
        $this->waregroup = $waregroup;
    }

    public function getAllegens(): string
    {
        return $this->allegens ?: '';
    }

    public function setAllegens(string $allegens): void
    {
        $this->allegens = $allegens;
    }

    public function getApnr(): string
    {
        return $this->apnr ?: '';
    }

    public function setApnr(string $apnr): void
    {
        $this->apnr = $apnr;
    }

    public function getAwards(): string
    {
        return $this->awards ?: '';
    }

    public function setAwards(string $awards): void
    {
        $this->awards = $awards;
    }

    public function getCaloricValue(): float
    {
        return $this->caloricValue ?: 0.0;
    }

    public function setCaloricValue(float $caloricValue): void
    {
        $this->caloricValue = $caloricValue;
    }

    public function getCultivation(): string
    {
        return $this->cultivation ?: '';
    }

    public function setCultivation(string $cultivation): void
    {
        $this->cultivation = $cultivation;
    }

    public function getLocation(): string
    {
        return $this->location ?: '';
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function getDevelopment(): string
    {
        return $this->development ?: '';
    }

    public function setDevelopment(string $development): void
    {
        $this->development = $development;
    }

    public function getDrinkingTemperature(): string
    {
        return $this->drinkingTemperature ?: '';
    }

    public function setDrinkingTemperature(string $drinkingTemperature): void
    {
        $this->drinkingTemperature = $drinkingTemperature;
    }

    public function getExpertise(): string
    {
        return $this->expertise ?: '';
    }

    public function setExpertise(string $expertise): void
    {
        $this->expertise = $expertise;
    }

    public function getGrounds(): string
    {
        return $this->grounds ?: '';
    }

    public function setGrounds(string $grounds): void
    {
        $this->grounds = $grounds;
    }

    public function hasSulfite(): bool
    {
        return $this->hasSulfite ?: false;
    }

    public function setHasSulfite(bool $hasSulfite): void
    {
        $this->hasSulfite = $hasSulfite;
    }

    public function isDrunken(): bool
    {
        return $this->isDrunken ?: false;
    }

    public function setIsDrunken(bool $isDrunken): void
    {
        $this->isDrunken = $isDrunken;
    }

    public function isStorable(): string
    {
        return $this->isStorable ?: '';
    }

    public function setIsStorable(string $isStorable): void
    {
        $this->isStorable = $isStorable;
    }

    public function getKind(): string
    {
        return $this->kind ?: '';
    }

    public function setKind(string $kind): void
    {
        $this->kind = $kind;
    }

    public function getAlcohol(): float
    {
        return $this->alcohol ?: 0.0;
    }

    public function setAlcohol(float $alcohol): void
    {
        $this->alcohol = $alcohol;
    }

    public function getLitre(): float
    {
        return $this->litre ?: 0.0;
    }

    public function setLitre(float $litre): void
    {
        $this->litre = $litre;
    }

    public function getLitrePrice(): float
    {
        return $this->litrePrice ?: 0.0;
    }

    public function setLitrePrice(float $litrePrice): void
    {
        $this->litrePrice = $litrePrice;
    }

    public function getNuances(): string
    {
        return $this->nuances ?: '';
    }

    public function setNuances(string $nuances): void
    {
        $this->nuances = $nuances;
    }

    public function getProtein(): float
    {
        return $this->protein ?: 0.0;
    }

    public function setProtein(float $protein): void
    {
        $this->protein = $protein;
    }

    public function getQuality(): string
    {
        return $this->quality ?: '';
    }

    public function setQuality(string $quality): void
    {
        $this->quality = $quality;
    }

    public function getSugar(): float
    {
        return $this->sugar ?: 0.0;
    }

    public function setSugar(float $sugar): void
    {
        $this->sugar = $sugar;
    }

    public function getTaste(): string
    {
        return $this->taste ?: '';
    }

    public function setTaste(string $taste): void
    {
        $this->taste = $taste;
    }

    public function getYear(): string
    {
        return $this->year ?: '';
    }

    public function setYear(string $year): void
    {
        $this->year = $year;
    }

    public function getAcid(): float
    {
        return $this->acid ?: 0.0;
    }

    public function setAcid(float $acid): void
    {
        $this->acid = $acid;
    }

    public function getImage1(): string
    {
        return $this->image1 ?: '';
    }

    public function setImage1(string $image1): void
    {
        $this->image1 = $image1;
    }

    public function getImage2(): string
    {
        return $this->image2 ?: '';
    }

    public function setImage2(string $image2): void
    {
        $this->image2 = $image2;
    }

    public function getImage3(): string
    {
        return $this->image3 ?: '';
    }

    public function setImage3(string $image3): void
    {
        $this->image3 = $image3;
    }

    public function getImage4(): string
    {
        return $this->image4 ?: '';
    }

    public function setImage4(string $image4): void
    {
        $this->image4 = $image4;
    }

    public function getBigImage1(): string
    {
        return $this->bigImage1 ?: '';
    }

    public function setBigImage1(string $bigImage1): void
    {
        $this->bigImage1 = $bigImage1;
    }

    public function getBigImage2(): string
    {
        return $this->bigImage2 ?: '';
    }

    public function setBigImage2(string $bigImage2): void
    {
        $this->bigImage2 = $bigImage2;
    }

    public function getBigImage3(): string
    {
        return $this->bigImage3 ?: '';
    }

    public function setBigImage3(string $bigImage3): void
    {
        $this->bigImage3 = $bigImage3;
    }

    public function getBigImage4(): string
    {
        return $this->bigImage4 ?: '';
    }

    public function setBigImage4(string $bigImage4): void
    {
        $this->bigImage4 = $bigImage4;
    }

    public function getCategory(): string
    {
        return $this->category ?: '';
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getManufacturer(): string
    {
        return $this->manufacturer ?: '';
    }

    public function setManufacturer(string $manufacturer): void
    {
        $this->manufacturer = $manufacturer;
    }

    public function getUnitId(): int
    {
        return $this->unitId ?: 0;
    }

    public function setUnitId(int $unitId): void
    {
        $this->unitId = $unitId;
    }

    public function getUnit(): string
    {
        return $this->unit ?: '';
    }

    public function setUnit(string $unit): void
    {
        $this->unit = $unit;
    }

    public function getUnitQuantity(): int
    {
        return $this->unitQuantity ?: 0;
    }

    public function setUnitQuantity(int $unitQuantity): void
    {
        $this->unitQuantity = $unitQuantity;
    }

    public function getEan(): string
    {
        return $this->ean ?: '';
    }

    public function setEan(string $ean): void
    {
        $this->ean = $ean;
    }

    public function getImportedAt(): string
    {
        return $this->importedAt;
    }

    public function setImportedAt(string $importedAt): void
    {
        $this->importedAt = $importedAt;
    }

    public function getStock(): int
    {
        return $this->stock ?: 0;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function getStockDate(): string
    {
        return $this->stockDate ? $this->stockDate->format('Y-m-d H:i:s') : '3000-01-01 00:00:01';
    }

    public function setStockDate(int $stockDate): void
    {
        $this->stockDate = $stockDate;
    }

    public function getBundle(): array
    {
        return $this->bundle;
    }

    public function setBundle(array $bundle): void
    {
        $this->bundle = $bundle;
    }
}
