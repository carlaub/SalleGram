-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:8880
-- Tiempo de generación: 15-04-2017 a las 01:14:22
-- Versión del servidor: 5.6.35
-- Versión de PHP: 7.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Base de datos: `pwgram`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Comment`
--

CREATE TABLE `Comment` (
  `id` int(11) NOT NULL,
  `content` text NOT NULL,
  `last_modified` datetime NOT NULL,
  `fk_user` int(11) NOT NULL,
  `fk_image` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `Comment`
--

INSERT INTO `Comment` (`id`, `content`, `last_modified`, `fk_user`, `fk_image`) VALUES
(2, 'dfds', '2017-04-16 00:00:00', 2, 2),
(33, 'dfds', '2017-04-16 00:00:00', 2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Image`
--

CREATE TABLE `Image` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `img_path` varchar(255) NOT NULL,
  `visits` int(11) NOT NULL,
  `private` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `likes` int(11) NOT NULL,
  `fk_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Image_likes`
--

CREATE TABLE `Image_likes` (
  `id` int(11) NOT NULL,
  `fk_user` int(11) NOT NULL,
  `fk_image` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `Image_likes`
--

INSERT INTO `Image_likes` (`id`, `fk_user`, `fk_image`) VALUES
(2, 67, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Notification`
--

CREATE TABLE `Notification` (
  `id` int(11) NOT NULL,
  `fk_user_dest` int(11) NOT NULL,
  `fk_user_src` int(11) NOT NULL,
  `type` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `User`
--

CREATE TABLE `User` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `password` varchar(12) NOT NULL,
  `profile_image` tinyint(4) NOT NULL,
  `active` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `User`
--

INSERT INTO `User` (`id`, `username`, `email`, `birthdate`, `password`, `profile_image`, `active`) VALUES
(1, 'lolo', 'sfds@sdfds.com', '2017-04-07', '123asdASD', 0, 0),
(2, 'lolos', 'sfds@sdfdds.com', '2017-04-07', '123asdASD', 0, 0),
(3, 'lolosa', 'sfds@sdfdds.coms', '2017-04-07', '123asdASD', 1, 0),
(4, 'cafff', 'sads@sdfds.com', '2017-04-11', '123asdASD', 1, 0),
(5, 'cafffe', 'sads@sddfds.com', '2017-04-11', '123asdASD', 1, 0),
(6, 'lols', 'fdsf@sdfds.com', '2017-04-06', '123asdASD', 1, 0),
(7, 'lolsa', 'fdsf@sdfsds.com', '2017-04-06', '123asdASD', 1, 0),
(8, 'lolsas', 'fdsf@sdfsds.dcom', '2017-04-06', '123asdASD', 1, 0),
(9, 'lolsasa', 'fdssf@sdfsds.dcom', '2017-04-06', '123asdASD', 1, 0),
(10, 'lolsasaa', 'fdsasf@sdfsds.dcom', '2017-04-06', '123asdASD', 1, 0),
(11, 'pepet', 'fddsasf@sdfsds.dcom', '2017-04-06', '123asdASD', 1, 0),
(12, 'pepet2', 'fddssasf@sdfsds.dcom', '2017-04-06', '123asdASD', 1, 0),
(13, 'pepet22', 'fddssasf@sdfsdws.dcom', '2017-04-06', '123asdASD', 1, 0),
(14, 'pepet222', 'fddsssasf@sdfsdws.dcom', '2017-04-06', '123asdASD', 1, 0),
(15, 'pepet2223', 'fddssseasf@sdfsdws.dcom', '2017-04-06', '123asdASD', 1, 0),
(16, 'pepet2225', 'sf@sdfsdws.dcom', '2017-04-06', '123asdASD', 1, 0),
(17, 'pepet22252', 'sssf@sdfsdws.dcom', '2017-04-06', '123asdASD', 1, 0),
(18, 'pep123', 'ssasf@sdfsdws.dcom', '2017-04-06', '123asdASD', 1, 0),
(19, '2pep123', 'ssassf@sdfsdws.dcom', '2017-04-06', '123asdASD', 1, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `Comment`
--
ALTER TABLE `Comment`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `Image`
--
ALTER TABLE `Image`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `Image_likes`
--
ALTER TABLE `Image_likes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `Notification`
--
ALTER TABLE `Notification`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `Comment`
--
ALTER TABLE `Comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
--
-- AUTO_INCREMENT de la tabla `Image`
--
ALTER TABLE `Image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `Image_likes`
--
ALTER TABLE `Image_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT de la tabla `Notification`
--
ALTER TABLE `Notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `User`
--
ALTER TABLE `User`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;