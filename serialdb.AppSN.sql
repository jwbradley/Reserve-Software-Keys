--
-- Table structure for table `AppSN`
--

DROP TABLE IF EXISTS `AppSN`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AppSN` (
  `IntNum` int(11) NOT NULL AUTO_INCREMENT,
  `SerialNumber` varchar(20) NOT NULL,
  `UserID` varchar(10) DEFAULT NULL,
  `First Name` varchar(45) DEFAULT NULL,
  `Last Name` varchar(50) DEFAULT NULL,
  `CheckedOut` timestamp NULL DEFAULT NULL,
  `WinSQLVer` varchar(7) NOT NULL,
  `CompanyName` varchar(20) NOT NULL,
  `Status` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`IntNum`,`SerialNumber`),
  UNIQUE KEY `SerialNumber_UNIQUE` (`SerialNumber`),
  UNIQUE KEY `IntNum_UNIQUE` (`IntNum`),
  UNIQUE KEY `UserID_UNIQUE` (`UserID`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=latin1 COMMENT='Serial numbers required to unlock the AppSN application.';