-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8880
-- Generation Time: May 10, 2017 at 08:49 PM
-- Server version: 5.6.35
-- PHP Version: 7.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `pwgram`
--

-- --------------------------------------------------------

--
-- Table structure for table `Comment`
--

CREATE TABLE `Comment` (
  `id` int(11) NOT NULL,
  `content` text NOT NULL,
  `last_modified` datetime NOT NULL,
  `fk_user` int(11) NOT NULL,
  `fk_image` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Image`
--

CREATE TABLE `Image` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `img_path` varchar(255) NOT NULL,
  `visits` int(11) NOT NULL,
  `private` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `likes` int(11) NOT NULL,
  `extension` varchar(255) NOT NULL,
  `fk_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `Image_likes`
--

CREATE TABLE `Image_likes` (
  `id` int(11) NOT NULL,
  `fk_user` int(11) NOT NULL,
  `fk_image` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Image_likes`
--

INSERT INTO `Image_likes` (`id`, `fk_user`, `fk_image`) VALUES
(1, 4, 48);

-- --------------------------------------------------------

--
-- Table structure for table `Notification`
--

CREATE TABLE `Notification` (
  `id` int(11) NOT NULL,
  `fk_user_dest` int(11) NOT NULL,
  `fk_user_src` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `fk_image` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` tinyint(4) NOT NULL,
  `active` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`id`, `username`, `email`, `birthdate`, `password`, `profile_image`, `active`) VALUES
(4, 'jorge1', 'jorgemt10@hotmail.com', '0012-12-12', '$2a$07$usesomadasdsadsadsadaepfVyqwEzvlYHfvOq6ONeArMWtk6DbrC', 1, 0),

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Comment`
--
ALTER TABLE `Comment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Image`
--
ALTER TABLE `Image`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Image_likes`
--
ALTER TABLE `Image_likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Notification`
--
ALTER TABLE `Notification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Comment`
--
ALTER TABLE `Comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `Image`
--
ALTER TABLE `Image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;
--
-- AUTO_INCREMENT for table `Image_likes`
--
ALTER TABLE `Image_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `Notification`
--
ALTER TABLE `Notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;