<?php
/**
 * Created by PhpStorm.
 * User: hakanbaysal
 * Date: 28.10.2018
 * Time: 02:06
 */

Class DB {
    protected $_config;
    public $dbc;

    /*
     * DB bilgilerinin set edilmesi.
     */
    public function __construct( ) {
        $this->_config = array('driver' => 'mysql','host' => 'localhost','dbname' => 'analytics','username' => 'root','password' => '123456');
        $this->getPDOConnection();
    }

    public function __destruct() {
        $this->dbc = NULL;
    }

   /*
    * PDO Connection metodu.
    */
    private function getPDOConnection() {
        if ($this->dbc == NULL) {
            $dsn = "" .
                $this->_config['driver'] .
                ":host=" . $this->_config['host'] .
                ";dbname=" . $this->_config['dbname'];
            try {
                $this->dbc = new PDO( $dsn, $this->_config[ 'username' ], $this->_config[ 'password' ] );
            } catch( PDOException $e ) {
                echo __LINE__.$e->getMessage();
            }
        }
    }

    /*
     * SQL sorgusunun çalıştırıldığı yer.
     */
    public function runQuery( $sql ) {
        $result = null;
        try {
            $sth = $this->dbc->prepare($sql);
            $sth->execute();
            $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo __LINE__.$e->getMessage();
        }
        return $result;
    }

    /*
     * Datanın objectClass'a set edilmesi için gerekli metot.
     */
    public function fetchObj( $sql , $obj ) {
        return $this->dbc->query($sql)->fetchAll(PDO::FETCH_CLASS, $obj);
    }

    /*
     * ObjectClass'tan insert etme işlemi.
     */
    public function insertViaObj( $obj ) {
        $sql = 'INSERT INTO analytics.datas ';

        $array = get_object_vars($obj);
        $params = "(`".implode("`,`", array_keys($array))."`)";
        $values = "('".implode("','", array_values($array))."')";
        $sql .= $params. ' VALUES '. $values;

        $this->runQuery($sql);
    }
}