-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Jul 05, 2016 at 07:24 PM
-- Server version: 5.5.42
-- PHP Version: 5.6.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: reading_list
--

-- --------------------------------------------------------

--
-- Table structure for table authorlists
--

CREATE TABLE authorlists (
  id int(11) NOT NULL,
  listid int(11) NOT NULL,
  authorid int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table authors
--

CREATE TABLE `authors` (
  id int(11) NOT NULL,
  fullname varchar(300) DEFAULT NULL,
  email varchar(100) NOT NULL,
  lms_id varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table authtokens
--

CREATE TABLE authtokens (
  id int(11) NOT NULL,
  token varchar(200) NOT NULL,
  timeout int(11) NOT NULL,
  tokentimestamp int(11) NOT NULL,
  credentialid int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table credentialconsumers
--

CREATE TABLE credentialconsumers (
  id int(11) NOT NULL,
  credentialid int(11) NOT NULL,
  consumerid varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table credentials
--

CREATE TABLE credentials (
  id int(11) NOT NULL,
  userid varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `profile` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table folders
--

CREATE TABLE folders (
  id int(11) NOT NULL,
  label text NOT NULL,
  listid int(11) NOT NULL,
  sortorder int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table lists
--

CREATE TABLE lists (
  id int(11) NOT NULL,
  institution varchar(200) DEFAULT NULL,
  linklabel text,
  course text,
  linkid text NOT NULL,
  private tinyint(4) NOT NULL DEFAULT '0',
  last_access timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  credentialconsumerid int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table oauth
--

CREATE TABLE oauth (
  id int(11) NOT NULL,
  oauth_consumer_key varchar(100) NOT NULL,
  secret varchar(100) NOT NULL,
  expires timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  libemail varchar(100) DEFAULT NULL,
  libname varchar(200) DEFAULT NULL,
  liblink varchar(400) DEFAULT NULL,
  liblogo varchar(400) DEFAULT NULL,
  `profile` varchar(20) DEFAULT NULL,
  userid varchar(20) DEFAULT NULL,
  `password` varchar(20) DEFAULT NULL,
  studentdata varchar(1) NOT NULL DEFAULT 'n',
  EDSlabel varchar(200) DEFAULT NULL,
  copyright text,
  copylist varchar(1) DEFAULT 'y',
  css varchar(200) DEFAULT NULL,
  forceft varchar(1) DEFAULT 'y',
  courselink varchar(1) DEFAULT 'y',
  quicklaunch varchar(1) DEFAULT 'n',
  newwindow varchar(1) DEFAULT 'n',
  firstftonly varchar(1) DEFAULT 'y',
  helppages text,
  proxyprefix varchar(100) DEFAULT NULL,
  proxyencode varchar(1) DEFAULT 'n',
  searchlabel varchar(200) DEFAULT 'Search Library Resources',
  empowered_roles varchar(200) DEFAULT 'Instructor,TeachingAssistant'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table readings
--

CREATE TABLE readings (
  id int(11) NOT NULL,
  listid int(11) NOT NULL,
  authorid int(11) NOT NULL,
  an text NOT NULL,
  db text NOT NULL,
  title text NOT NULL,
  priority int(11) NOT NULL,
  notes text,
  url text NOT NULL,
  `type` int(11) NOT NULL,
  instruct text NOT NULL,
  folderid int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table studentaccess
--

CREATE TABLE studentaccess (
  id int(11) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  email varchar(200) DEFAULT NULL,
  listid int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table studentreading
--

CREATE TABLE studentreading (
  id int(11) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  email varchar(200) DEFAULT NULL,
  readingid int(11) NOT NULL,
  user_id varchar(200) DEFAULT NULL,
  accessed_time datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table authorlists
--
ALTER TABLE authorlists
  ADD PRIMARY KEY (id);

--
-- Indexes for table authors
--
ALTER TABLE authors
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY email (email);

--
-- Indexes for table authtokens
--
ALTER TABLE authtokens
  ADD PRIMARY KEY (id);

--
-- Indexes for table credentialconsumers
--
ALTER TABLE credentialconsumers
  ADD PRIMARY KEY (id);

--
-- Indexes for table credentials
--
ALTER TABLE credentials
  ADD PRIMARY KEY (id);

--
-- Indexes for table folders
--
ALTER TABLE folders
  ADD PRIMARY KEY (id);

--
-- Indexes for table lists
--
ALTER TABLE lists
  ADD PRIMARY KEY (id);

--
-- Indexes for table oauth
--
ALTER TABLE oauth
  ADD PRIMARY KEY (id);

--
-- Indexes for table readings
--
ALTER TABLE readings
  ADD PRIMARY KEY (id);

--
-- Indexes for table studentaccess
--
ALTER TABLE studentaccess
  ADD PRIMARY KEY (id);

--
-- Indexes for table studentreading
--
ALTER TABLE studentreading
  ADD PRIMARY KEY (id);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table authorlists
--
ALTER TABLE authorlists
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table authors
--
ALTER TABLE authors
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table authtokens
--
ALTER TABLE authtokens
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table credentialconsumers
--
ALTER TABLE credentialconsumers
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table credentials
--
ALTER TABLE credentials
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table folders
--
ALTER TABLE folders
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table lists
--
ALTER TABLE lists
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table oauth
--
ALTER TABLE oauth
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table readings
--
ALTER TABLE readings
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table studentaccess
--
ALTER TABLE studentaccess
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table studentreading
--
ALTER TABLE studentreading
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
