<?php

namespace CodeGenerator;

class FunctionBlock extends Block
{
    /** @var string|null */
    protected $name;
    /** @var string */
    protected $code;
    /** @var ParameterBlock[] */
    private $parameters = [];

    /**
     * @param callable|string|null $body
     */
    public function __construct($body = null)
    {
        if (null !== $body) {
            if ($body instanceof \Closure) {
                $this->extractFromClosure($body);
            } else {
                $this->setCode($body);
            }
        }
    }

    /**
     * @param \Closure $closure
     */
    public function extractFromClosure(\Closure $closure)
    {
        $this->extractFromReflection(new \ReflectionFunction($closure));
    }

    /**
     * @param \ReflectionFunctionAbstract $reflection
     */
    public function extractFromReflection(\ReflectionFunctionAbstract $reflection)
    {
        $this->setBodyFromReflection($reflection);
        $this->setParametersFromReflection($reflection);
        $this->setDocBlockFromReflection($reflection);
    }

    /**
     * @param \ReflectionFunctionAbstract $reflection
     */
    protected function setBodyFromReflection(\ReflectionFunctionAbstract $reflection)
    {
        /** @var $reflection \ReflectionMethod */
        if (is_a($reflection, '\\ReflectionMethod') && $reflection->isAbstract()) {
            $this->code = null;

            return;
        }
        $file = new \SplFileObject($reflection->getFileName());
        $file->seek($reflection->getStartLine() - 1);

        $code = '';
        while ($file->key() < $reflection->getEndLine()) {
            $code .= $file->current();
            $file->next();
        }

        $begin = strpos($code, 'function');
        $code = substr($code, $begin);

        $begin = strpos($code, '{');
        $end = strrpos($code, '}');
        $code = substr($code, $begin + 1, $end - $begin - 1);
        $code = preg_replace('/^\s*[\r\n]+/', '', $code);
        $code = preg_replace('/[\r\n]+\s*$/', '', $code);

        if (!trim($code)) {
            $code = null;
        }
        $this->setCode($code);
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        if (null !== $code) {
            $code = $this->outdent((string)$code, true);
        }
        $this->code = $code;
    }

    /**
     * @param \ReflectionFunctionAbstract $reflection
     */
    protected function setParametersFromReflection(\ReflectionFunctionAbstract $reflection)
    {
        foreach ($reflection->getParameters() as $reflectionParameter) {
            $parameter = ParameterBlock::buildFromReflection($reflectionParameter);
            $this->addParameter($parameter);
        }
    }

    /**
     * @param ParameterBlock $parameter
     *
     * @throws \Exception
     */
    public function addParameter(ParameterBlock $parameter)
    {
        if (array_key_exists($parameter->getName(), $this->parameters)) {
            throw new \Exception('Parameter `' . $parameter->getName() . '` is already set.');
        }
        $this->parameters[$parameter->getName()] = $parameter;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string)$name;
    }

    /**
     * @return string
     */
    protected function dumpContent()
    {
        return $this->dumpLine(
            $this->dumpHeader() . $this->dumpBody()
        );
    }

    /**
     * @return string
     */
    protected function dumpHeader()
    {
        $content = 'function';
        if ($this->name) {
            $content .= ' ' . $this->name;
        }
        $content .= '(';
        $content .= implode(', ', $this->parameters);
        $content .= ')';

        return $content;
    }

    /**
     * @return string
     */
    protected function dumpBody()
    {
        $code = $this->code;
        if ($code) {
            $code = $this->indent($code);
        }

        return $this->dumpLine('', '{', $code, '}');
    }
}
