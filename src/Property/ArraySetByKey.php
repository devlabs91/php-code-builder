<?php

namespace Swaggest\PhpCodeBuilder\Property;

use Swaggest\PhpCodeBuilder\PhpClassProperty;
use Swaggest\PhpCodeBuilder\PhpDocType;
use Swaggest\PhpCodeBuilder\PhpFlags;
use Swaggest\PhpCodeBuilder\PhpFunction;
use Swaggest\PhpCodeBuilder\PhpNamedVar;
use Swaggest\PhpCodeBuilder\Types\OrType;
use Swaggest\PhpCodeBuilder\Types\ArrayOf;
use Swaggest\PhpCodeBuilder\PhpStdType;

class ArraySetByKey extends PhpFunction
{
    /**
     * Getter constructor.
     * @param PhpClassProperty $property
     * @param bool $fluent
     */
    public function __construct(PhpClassProperty $property, $fluent = true)
    {
        $name = $property->getNamedVar()->getName();
        parent::__construct(
            'set' . ucfirst($name).'ByKey',
            PhpFlags::VIS_PUBLIC
        );

        $this->skipCodeCoverage = true;

        $namedVar = $property->getNamedVar();
        if($property->getNamedVar()->getType() instanceof OrType ) {
            foreach($property->getNamedVar()->getType()->getTypes() AS $type) {
                if($type instanceof ArrayOf) {
                    $namedVar = new PhpNamedVar( $type->getType()->getName(), $type->getType() );
                }
            }
        }
        
        $this->addArgument( new PhpNamedVar( 'key', PhpStdType::string() ) );
        $this->addArgument($namedVar);

        $body = <<<PHP
if(!is_array(\$this->{$name})) { \$this->{$name} = []; }
\$this->{$name}[\$key] = \${$namedVar->getName()};

PHP;

        if ($fluent) {
            $this->setResult(PhpDocType::thisType());
            $body .= <<<PHP
return \$this;

PHP;

        }

        $this->setBody($body);

    }
}