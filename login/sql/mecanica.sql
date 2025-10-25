-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2025 at 02:42 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mecanica`
--

-- --------------------------------------------------------

--
-- Table structure for table `item_servico`
--

CREATE TABLE `item_servico` (
  `id` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `descricao` text NOT NULL,
  `valor` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pedido_servico`
--

CREATE TABLE `pedido_servico` (
  `id` int(11) NOT NULL,
  `id_veiculo` int(11) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Em andamento'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pedido_servico`
--

INSERT INTO `pedido_servico` (`id`, `id_veiculo`, `data_criacao`, `observacoes`, `status`) VALUES
(3, 3, '2025-10-25 12:33:52', 'Troca de óleo e filtro', 'Em andamento');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `telefone` varchar(30) NOT NULL,
  `cpf_usuario` varchar(80) NOT NULL,
  `placa_hash` varchar(255) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('admin','user') NOT NULL DEFAULT 'user',
  `placa` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `telefone`, `cpf_usuario`, `placa_hash`, `criado_em`, `tipo`, `placa`) VALUES
(1, 'cotonho', '(43) 56436-5436', '123.456.789-00', '$2y$10$HBr5X1ThC8f9/ODwdRuNeugn6lVY9XRLl8ySMlFzeiixhiFISjACW', '2025-10-01 22:36:54', 'admin', 'ABC-1234'),
(2, 'pedro', '(64) 13265-2457', '987.654.321-00', '$2y$10$dJhyVFFEXIB/C0WvuMvype0gokg0Op6Hy5JbnL1BNC3v/MP91gjH6', '2025-10-01 23:13:39', 'user', 'ABC-5678');

-- --------------------------------------------------------

--
-- Table structure for table `veiculo`
--

CREATE TABLE `veiculo` (
  `id` int(11) NOT NULL,
  `cpf_usuario` varchar(80) NOT NULL,
  `placa` varchar(10) NOT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `ano` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `veiculo`
--

INSERT INTO `veiculo` (`id`, `cpf_usuario`, `placa`, `modelo`, `ano`) VALUES
(3, '123.456.789-00', 'ABC-1234', 'Teste', 2020);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `item_servico`
--
ALTER TABLE `item_servico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pedido` (`id_pedido`);

--
-- Indexes for table `pedido_servico`
--
ALTER TABLE `pedido_servico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_veiculo` (`id_veiculo`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf_usuario` (`cpf_usuario`);

--
-- Indexes for table `veiculo`
--
ALTER TABLE `veiculo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cpf_usuario` (`cpf_usuario`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `item_servico`
--
ALTER TABLE `item_servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pedido_servico`
--
ALTER TABLE `pedido_servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `veiculo`
--
ALTER TABLE `veiculo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `item_servico`
--
ALTER TABLE `item_servico`
  ADD CONSTRAINT `item_servico_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido_servico` (`id`);

--
-- Constraints for table `pedido_servico`
--
ALTER TABLE `pedido_servico`
  ADD CONSTRAINT `pedido_servico_ibfk_1` FOREIGN KEY (`id_veiculo`) REFERENCES `veiculo` (`id`);

--
-- Constraints for table `veiculo`
--
ALTER TABLE `veiculo`
  ADD CONSTRAINT `veiculo_ibfk_1` FOREIGN KEY (`cpf_usuario`) REFERENCES `usuarios` (`cpf_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
