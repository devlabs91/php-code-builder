<?php

namespace Swaggest\PhpCodeBuilder\Property;

use Swaggest\PhpCodeBuilder\PhpClassProperty;
use Swaggest\PhpCodeBuilder\PhpFlags;
use Swaggest\PhpCodeBuilder\PhpFunction;
use Swaggest\PhpCodeBuilder\PhpNamedVar;
use Swaggest\PhpCodeBuilder\Types\OrType;
use Swaggest\PhpCodeBuilder\Types\ArrayOf;
use Swaggest\PhpCodeBuilder\PhpStdType;

class ArrayGetByKey extends PhpFunction
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
            'get'.ucfirst($name).'ByKey',
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

        $body = <<<PHP
if(is_array(\$this->{$name}) && key_exists(\$key, \$this->{$name}) ) { return \$this->{$name}[\$key]; }

PHP;

        $orType = new OrType();
        $orType->add( $namedVar->getType() );
        $orType->add( PhpStdType::null() );
        $this->setResult($orType);
        
        $body .= <<<PHP
return null;

PHP;

        $this->setBody($body);

    }
}