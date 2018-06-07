<?php

namespace edwrodrig\cnv_parser;

class Saver {

public $dao;
public $parser;
public $depth_column;
public $ignored_columns = [];

function __construct() {
  $this->dao = \sealines\Config::get_query_dao();
}

function load() {
  try {
    $this->dao->pdo->beginTransaction();
    $sensor_map = [];
    foreach ( $this->parser->sensors as $sensor ) {
      $id_sensor = $this->dao->create_sensor($sensor['name'], $sensor['metric']['type'], $sensor['unit']['unit'] ?? null, json_encode($sensor));
      $sensor_map[] = $id_sensor;
    }

    $metadata = [
      'properties' => $this->parser->properties,
      'info' => $this->parser->info
    ];

    $id_set = $this->dao->create_set(
      $metadata['properties']['Station'],
      'Station',
      json_encode($metadata),
      $sensor_map[$this->depth_column]
    );

    $this->dao->set_set_location(
      $metadata['properties']['NMEA Latitude'],
      $metadata['properties']['NMEA Longitude'],
      Parser::normalize_date($metadata['properties']['System UTC']),
      $id_set
    );

    foreach ( $sensor_map as $index => $id_sensor ) {
      if ( $this->is_column_ignored($index) ) continue;
      $this->dao->register_sensor_to_set($id_set, $id_sensor);
    }

    foreach ( $this->parser->traverse() as $row ) {
      $pressure = $row[$this->depth_column];
      foreach ( $row as $column_index => $measurement ) {
        if ( $this->is_column_ignored($column_index) ) continue;
        $this->dao->add_measurement_without_pos(
          $pressure,
          $sensor_map[$column_index],
          $measurement,
          $id_set
        );
      }
    }
    $this->dao->pdo->commit();
    return $id_set;
  } catch ( \Exception $e ) {
    $this->dao->pdo->rollBack();
    throw $e;
  }
}

function is_column_ignored($index) {
  if ( $index == $this->depth_column ) return true;
  if ( in_array($index, $this->ignored_columns) ) return true;
  return false;
}

}
