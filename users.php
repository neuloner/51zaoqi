<?php
/***************************************************************************
 * 
 * Copyright (c) 2013 Baidu.com, Inc. All Rights Reserved
 * $Id$ 
 * 
 **************************************************************************/
 
 
 
/**
 * @file users.php
 * @author caojiandong(caojiandong@baidu.com)
 * @date 2013/02/25 17:39:24
 * @version $Revision$ 
 * @brief 
 *  
 **/
require_once('lib/db.php');
class Users {
    private $_objDb;
    public function __construct() {
        $this->_objDb = new Db(); 
    }

    public function getAllUsersId() {
        $result = array();
        $sql = "select id from users"; 
        $res = $this->_objDb->query($sql); 
        while($row = mysqli_fetch_row($res)) {
            $result[] = $row[0];
        }
        return $result;
    }

    public function getSixUsersId() {
        $result = array();
        $sql = "select id from users,role where users.id=role.userid and role = 1"; 
        $res = $this->_objDb->query($sql); 
        while($row = mysqli_fetch_row($res)) {
            $result[] = $row[0];
        }
        return $result;
    }

    public function getSevenUsersId() {
        $result = array();
        $sql = "select id from users,role where users.id=role.userid and role = 2"; 
        $res = $this->_objDb->query($sql); 
        while($row = mysqli_fetch_row($res)) {
            $result[] = $row[0];
        }
        return $result;
    }


}




/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
