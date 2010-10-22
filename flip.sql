-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas wygenerowania: 22 Paź 2010, 10:54
-- Wersja serwera: 5.0.91
-- Wersja PHP: 5.2.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Baza danych: `lodz_flip`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `aims`
--

CREATE TABLE `aims` (
  `aim_id` tinyint(3) unsigned NOT NULL auto_increment,
  `aim` char(15) NOT NULL default '',
  PRIMARY KEY  (`aim_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `contacts`
--

CREATE TABLE `contacts` (
  `contact_id` int(10) unsigned NOT NULL auto_increment,
  `organisation_id` int(10) unsigned NOT NULL default '0',
  `user_id` int(10) unsigned NOT NULL default '0',
  `project_id` int(10) unsigned NOT NULL default '0',
  `date` date NOT NULL default '0000-00-00',
  `type` enum('telefon','spotkanie') NOT NULL default 'telefon',
  `contact_person` varchar(30) NOT NULL default '',
  `contact_function` varchar(40) NOT NULL default '',
  `comments` text NOT NULL,
  `aim_id` tinyint(3) unsigned NOT NULL default '0',
  `next_contact_type` enum('telefon','spotkanie') default NULL,
  `next_contact_date` date default NULL,
  PRIMARY KEY  (`contact_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `help_categories`
--

CREATE TABLE `help_categories` (
  `category_id` smallint(6) NOT NULL auto_increment,
  `label` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `help_questions`
--

CREATE TABLE `help_questions` (
  `question_id` smallint(6) NOT NULL auto_increment,
  `category_id` smallint(6) NOT NULL default '0',
  `question` varchar(255) NOT NULL default '',
  `answer` text NOT NULL,
  `helper_id` int(11) NOT NULL default '0',
  `date` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`category_id`,`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `oc`
--

CREATE TABLE `oc` (
  `oc_member_id` int(10) unsigned NOT NULL default '0',
  `project_id` int(10) unsigned NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `organisations`
--

CREATE TABLE `organisations` (
  `organisation_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `street` varchar(40) default NULL,
  `city` varchar(25) default NULL,
  `phone` varchar(12) default NULL,
  `fax` varchar(12) default NULL,
  `www` varchar(100) default NULL,
  `profile` tinytext,
  `date` date NOT NULL default '0000-00-00',
  `updater_id` int(11) default NULL,
  PRIMARY KEY  (`organisation_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `projects`
--

CREATE TABLE `projects` (
  `project_id` int(10) unsigned NOT NULL auto_increment,
  `name` char(25) NOT NULL default '',
  `ocp_id` int(10) unsigned NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`project_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `projects_orgs`
--

CREATE TABLE `projects_orgs` (
  `project_id` int(11) NOT NULL default '0',
  `organisation_id` int(11) NOT NULL default '0',
  `oc_responsible_id` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `login` char(30) NOT NULL default '',
  `status` tinyint(3) unsigned NOT NULL default '0',
  `password` char(16) NOT NULL default '',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
