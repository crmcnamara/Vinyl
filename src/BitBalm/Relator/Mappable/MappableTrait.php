<?php

namespace BitBalm\Relator\Mappable;


use Exception;
use InvalidArgumentException;

use BitBalm\Relator\Mappable;
use BitBalm\Relator\RecordSet;

Trait MappableTrait 
{
    protected static $table_name;
    
    protected $record_values = [];
    
    public function asArray() : array
    {
        return $this->record_values;
    }
    
    public function setValues( array $values ) : Mappable 
    {
        $this->record_values = array_replace( (array) $this->record_values, $values ) ;
        return $this;
    }
    
    public function newRecord() : Mappable
    {
        return new static;
    }
    
    public function asRecordSet( RecordSet $recordset = null ) : RecordSet
    {
        return $recordset ? new $recordset([ $this ]) : new RecordSet\Simple([ $this ]);
    }
    
    public function getTableName() : string
    {
        #TODO: throw Exception if null? Otherwise, a TypeError will be
        return static::$table_name;
    }
    
    public function setTableName( string $table_name ) : Mappable
    {
        $existing_name = static::$table_name;
        
        if ( $existing_name === $table_name ) { return $this; }
        
        if ( !is_null($existing_name) )  {
            throw new InvalidArgumentException(
                "The table name for this object is already set to: {$existing_name}. "
              );
        }
        
        static::$table_name = $table_name;
        
        return $this;
        
    }
}