<?php

namespace BitBalm\Relator\Recorder;

#use PDO;
use Exception;
use InvalidArgumentException;

use Aura\SqlSchema\SchemaInterface;

use BitBalm\Relator\PDO\BaseMapper;
use BitBalm\Relator\Recorder;
use BitBalm\Relator\Recordable;


class PDO extends BaseMapper implements Recorder
{
    
    public function loadRecord( Recordable $record, $record_id ) : Recordable 
    {
        $table  = $this->validTable($record->getTableName());
        $prikey = $this->validColumn( $table, $record->getPrimaryKeyName() );
        
        $querystring = "SELECT * from {$table} where {$prikey} = ? ";
        
        $statement = $this->pdo->prepare( $querystring );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        $statement->execute([ $record_id ]);
        $results = $statement->fetchAll();
        
        if ( count($results) >1 ) { 
            throw new Exception( "Multiple {$table} records loaded for {$prikey}: {$record_id} " ) ; 
        }
        
        if ( count($results) <1 ) { 
            throw new InvalidArgumentException( "No {$table} records found for {$prikey}: {$record_id} " ) ; 
        }
        
        $values = current($results);
        
        $record
            ->setValues($values)
            ->setUpdateId( $values[ $record->getPrimaryKeyName() ] ?? null );
        
        return $record;
    }
    
    public function saveRecord( Recordable $record ) : Recordable 
    {
        if ( ! is_null($record->getUpdateId()) ) {
            $this->updateRecord($record);
            
        } else {
            $this->insertRecord($record);
        }
        
        $this->loadRecord( $record, $record->getUpdateId() );
        
        return $record;
    }
    
    protected function insertRecord( Recordable $record ) : Recordable
    {
        
        $table = $this->validTable($record->getTableName());
        $values = $record->asArray();
        foreach ( $values as $column => $value ) { $this->validColumn( $table, $column ); }
                
        $querystring = 
            "INSERT into {$table} ( "
                . implode( ' , ', array_keys( $values ) )
            ." ) VALUES ( "
                . implode( ' , ', array_pad( [], count($values), '?' ) )
            ." ) ";
            
        $statement = $this->pdo->prepare( $querystring );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        $statement->execute(array_values( $values ));
        $inserted_id = $this->pdo->lastInsertId();
        
        $record->setUpdateId( $inserted_id );
        
        return $record;
    }
    
    protected function updateRecord( Recordable $record ) : Recordable
    {
        $table = $this->validTable($record->getTableName());
        $prikey = $this->validColumn( $table, $record->getPrimaryKeyName() );
        $values = $record->asArray();
        foreach ( $values as $column => $value ) { $this->validColumn( $table, $column ); }
        $update_id = $record->getUpdateId();
        
        $setstrings = [];
        foreach ( $values as $column => $value ) {
            $setstrings[$column] = " {$column} = ? ";
        }
        
        $querystring = 
            "UPDATE {$table} set "
                . implode( ' , ', $setstrings )
            ." WHERE {$prikey} = ? ";
            
        $statement = $this->pdo->prepare( $querystring );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $query_values = array_values( $values );
        $query_values[] = $update_id;
        
        $statement->execute($query_values);
        $affected = $statement->rowCount();
        
        if ( array_key_exists( $prikey, $values ) ) { $update_id = $values[$prikey]; }
        $record->setUpdateId( $update_id );
        
        return $record;
    }
    
    public function deleteRecord( Recordable $record ) 
    {
        $table = $this->validTable($record->getTableName());
        $prikey = $this->validColumn( $table, $record->getPrimaryKeyName() );
        $update_id = $record->getUpdateId();
        
        $querystring = "DELETE from {$table} WHERE {$prikey} = ? ";
            
        $statement = $this->pdo->prepare( $querystring );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        $statement->execute([ $update_id ]);
        $affected = $statement->rowCount();
        
        return $affected;
    }
}
