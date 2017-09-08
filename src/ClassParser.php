<?php

namespace EloquentAnnotations;

class ClassParser
{
    /**
     * @var string|null
     */
    protected $namespace;

    /**
     * @var string|null
     */
    protected $class;

    /**
     * @var string|null
     */
    protected $parent;

    /**
     * @param string $filePath
     */
    public function parse($filePath)
    {
        $this->reset();

        $content = file_get_contents($filePath);
        $tokens = token_get_all($content);

        foreach ($tokens as $index => $tokenData) {

            if ($this->isToken($tokenData, T_NAMESPACE) && !$this->getNamespace()) {
                $this->namespace = $this->extractNamespace($index, $tokens);
            }

            if ($this->isToken($tokenData, [T_CLASS, T_INTERFACE]) && !$this->getClass()) {
                $this->class = $this->extractClass($index + 2, $tokens);
            }

            if ($this->isToken($tokenData, T_EXTENDS) && !$this->getParent()) {
                $this->parent = $this->extractClass($index + 2, $tokens);
            }
        }
    }

    public function reset()
    {
        $this->namespace = null;
        $this->class = null;
        $this->parent = null;
    }

    /**
     * @return null|string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return null|string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return null|string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getFullQualifiedClass()
    {
        return trim($this->namespace . '\\' . $this->class, '\\');
    }

    /**
     * @param int $index
     * @param array $tokens
     * @return string
     */
    protected function extractNamespace($index, $tokens)
    {
        return $this->extractValue($index, $tokens, [T_STRING, T_NS_SEPARATOR], ';');
    }

    /**
     * @param int $index
     * @param array $tokens
     * @return string
     */
    protected function extractClass($index, $tokens)
    {
        return $this->extractValue($index, $tokens, T_STRING, T_WHITESPACE);
    }

    /**
     * @param int $index
     * @param array $tokens
     * @param int|array $keep
     * @param int|array $stop
     * @return null|string
     */
    protected function extractValue($index, $tokens, $keep, $stop)
    {
        $value = null;
        $max = count($tokens);

        for ($i = $index; $i < $max; $i++) {

            $tokenData = $tokens[$i];

            if ($this->isToken($tokenData, $keep)) {
                $value .= $tokenData[1];
            }

            if ($this->isToken($tokenData, $stop)) {
                break;
            }
        }

        return $value;
    }

    /**
     * @param string|array $tokenData
     * @param int|string|array $tokenIdentifier
     * @return bool
     */
    protected function isToken($tokenData, $tokenIdentifier)
    {
        if (!is_array($tokenData)) {
            $tokenData = [$tokenData];
        }

        if (!is_array($tokenIdentifier)) {
            $tokenIdentifier = [$tokenIdentifier];
        }

        return in_array($tokenData[0], $tokenIdentifier);
    }
}