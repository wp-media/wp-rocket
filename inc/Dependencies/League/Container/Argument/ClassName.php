<?php declare(strict_types=1);

namespace WP_Rocket\Dependencies\League\Container\Argument;

class ClassName implements ClassNameInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * Construct.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName() : string
    {
        return $this->value;
    }
}
