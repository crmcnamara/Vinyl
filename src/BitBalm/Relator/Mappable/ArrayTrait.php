<?php

namespace BitBalm\Relator\Mappable;

use BitBalm\Relator\Mappable;


/**
 * Implements \ArrayAccess for Relator\Mappable classes. 
 * You will need to declare the implementation yourself, on your own classes, however.
 */
Trait ArrayTrait 
{   
    // setValues() is in the Mappable interface, and implemented in MappableTrait.
    abstract public function setValues( array $values );
    
    public function offsetExists ( $offset )
    {
        return array_key_exists( $offset, $this->asArray() );
    }
    
    public function offsetGet ( $offset ) 
    {
        return $this->asArray()[$offset];
    }
    
    public function offsetSet ( $offset , $value )
    {
        $this->setValues([ $offset => $value ]);
    }
    
    public function offsetUnset ( $offset )
    {
        unset( $this->record_values[$offset] );
    }
    
}
