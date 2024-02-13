<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Response;

abstract class ResponseAbstract implements ResponseInterface
{
    protected $data = array();

    protected $isSuccessful = false;

    /** @var string */
    protected $error;

    public function __construct(string $content)
    {
        $xmlObject = @simplexml_load_string($content) or false;

        if(false != $xmlObject){
            $this->data = $this->xmlObjectToArray($xmlObject);
            $this->data = $this->sanitizeSingleArticle($this->data);
            $this->data = $this->unserialize($this->data);
            if (!isset($this->data['text'])) {
                $this->isSuccessful = true;
            } else {
                $this->error = var_export($this->data, true);
            }
        } else {
            $this->error = $content;
        }
    }

    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, $value) : void
    {
        $this->data[$key] = $value;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    private function xmlObjectToArray($xmlObjectOrArray) : array
    {
        $return = (array) $xmlObjectOrArray;
        foreach ($return as $key => $value) {
            if ($value instanceof \SimpleXMLElement) {
                $return[$key] = $this->xmlObjectToArray($value);
            }
            if (is_array($value)) {
                $return[$key] = $this->xmlObjectToArray($value);
            }
        }
        return $return;
    }

    private function unserialize(array $data): array
    {
        $return = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $return[$key] = $this->unserialize($value);
            }
            if (!is_array($value)) {
                $return[$key] = $value;
                $unserialized = false;
                if (substr($value, 0, 2) == 'a:') {
                    $unserialized = @unserialize($value);
                }
                if(is_array($unserialized)) {
                    $return[$key] = $unserialized;
                }
            }
        }
        return $return;
    }

    /** if the webshop returns only one item, this has no index before data */
    private function sanitizeSingleArticle(array $data): array
    {
        if (isset($data['item']) && !is_numeric(key($data['item']))) {
            $articleData = $data['item'];
            unset($data['item']);
            $data['item'][0] = $articleData;
        }
        return $data;
    }
}
