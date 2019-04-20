/*
 Navicat MySQL Data Transfer

 Source Server         : Maria Suitup
 Source Server Type    : MariaDB
 Source Server Version : 100404
 Source Host           : localhost:3306
 Source Schema         : suitup

 Target Server Type    : MariaDB
 Target Server Version : 100404
 File Encoding         : 65001

 Date: 13/04/2019 23:42:07
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for album
-- ----------------------------
DROP TABLE IF EXISTS `album`;
CREATE TABLE `album`  (
  `pk_album` int(11) NOT NULL AUTO_INCREMENT,
  `fk_artista` int(11) NOT NULL,
  `nome` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `genero` enum('Rock','Classica','Pop','Sertanejo','Gospel','Reggae','Bluegrass','Country') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `ano` varchar(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `votos` int(11) NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created` timestamp(0) NOT NULL DEFAULT current_timestamp,
  `updated` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`pk_album`) USING BTREE,
  INDEX `fk_artista`(`fk_artista`) USING BTREE,
  CONSTRAINT `album_ibfk_1` FOREIGN KEY (`fk_artista`) REFERENCES `artista` (`pk_artista`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of album
-- ----------------------------
INSERT INTO `album` VALUES (1, 1, 'Johnny Cash with His Hot and Blue Guitar', 'Country', '1957', 4311, 1, '2019-04-14 02:36:33', NULL);
INSERT INTO `album` VALUES (2, 1, 'At Folsom Prison', 'Country', '1968', 82736, 1, '2019-04-14 02:39:17', NULL);

-- ----------------------------
-- Table structure for artista
-- ----------------------------
DROP TABLE IF EXISTS `artista`;
CREATE TABLE `artista`  (
  `pk_artista` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created` timestamp(0) NOT NULL DEFAULT current_timestamp,
  `updated` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`pk_artista`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of artista
-- ----------------------------
INSERT INTO `artista` VALUES (1, 'Johnny Cash', 1, '2019-04-14 02:35:41', NULL);
INSERT INTO `artista` VALUES (2, 'Natiruts', 1, '2019-04-14 02:35:47', NULL);
INSERT INTO `artista` VALUES (3, 'Bob Marley', 1, '2019-04-14 02:35:52', NULL);
INSERT INTO `artista` VALUES (4, 'AC DC', 1, '2019-04-14 02:35:57', NULL);

-- ----------------------------
-- Table structure for musica
-- ----------------------------
DROP TABLE IF EXISTS `musica`;
CREATE TABLE `musica`  (
  `pk_musica` int(11) NOT NULL AUTO_INCREMENT,
  `fk_album` int(11) NOT NULL,
  `nome` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `url` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created` timestamp(0) NOT NULL DEFAULT current_timestamp,
  `updated` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`pk_musica`) USING BTREE,
  INDEX `fk_album`(`fk_album`) USING BTREE,
  CONSTRAINT `musica_ibfk_1` FOREIGN KEY (`fk_album`) REFERENCES `album` (`pk_album`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of musica
-- ----------------------------
INSERT INTO `musica` VALUES (1, 1, 'Cry! Cry! Cry!', 'https://www.youtube.com/watch?v=XHaVmFKnK7w', 1, '2019-04-14 02:40:17', NULL);
INSERT INTO `musica` VALUES (2, 1, 'Walk the line', 'https://www.youtube.com/watch?v=KHF9itPLUo4', 1, '2019-04-14 02:40:45', NULL);

SET FOREIGN_KEY_CHECKS = 1;
