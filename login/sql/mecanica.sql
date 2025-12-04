-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13/11/2025 às 17:49
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `mecanica`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `item_servico`
--

CREATE TABLE `item_servico` (
  `id` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `descricao` text NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status_item` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `item_servico`
--

INSERT INTO `item_servico` (`id`, `id_pedido`, `descricao`, `valor`, `status_item`) VALUES
(1, 4, 'teste', 100.00, NULL),
(3, 5, 'teste', 100.00, 'Em andamento');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido_servico`
--

CREATE TABLE `pedido_servico` (
  `id` int(11) NOT NULL,
  `id_veiculo` int(11) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Em andamento'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pedido_servico`
--

INSERT INTO `pedido_servico` (`id`, `id_veiculo`, `data_criacao`, `observacoes`, `status`) VALUES
(4, 6, '2025-11-12 12:22:18', 'teste', 'Em andamento'),
(5, 6, '2025-11-12 17:38:17', NULL, 'Aguardando');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `telefone` varchar(30) NOT NULL,
  `cpf_usuario` varchar(80) NOT NULL,
  `placa_hash` varchar(255) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo` enum('admin','user') NOT NULL DEFAULT 'user',
  `placa` varchar(10) NOT NULL,
  `senha_hash` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `telefone`, `cpf_usuario`, `placa_hash`, `criado_em`, `tipo`, `placa`, `senha_hash`) VALUES
(3, 'teste teste', '(11) 11111-1111', '111.111.111-11', '$2y$10$XmfCu5/.MC/JaSutDJbutO1azAemLfV6zUyD5CT5KSllSrQ0JQ64m', '2025-11-11 16:58:15', 'admin', 'PCD-1234', '$2y$10$mfSgz7CfVERLO1WeqTldl.AUWN8VCTtdXLOoI1hqtKcQ2pn5NETTC'),
(4, 'Pedro', '(22) 22222-2222', '222.222.222-22', '$2y$10$iO9QvYw00xS0nU1feZC6LOTFaiFkMHj4RTFSw25sHR1IyaU7bAKKy', '2025-11-11 17:26:23', 'user', 'ABC-1234', '$2y$10$DAsVFR0OuOulg.z4ZH77aedQ9pZSEttO2cSAsIvFXj6VZ8v2CWUEG');

-- --------------------------------------------------------

--
-- Estrutura para tabela `veiculo`
--

CREATE TABLE `veiculo` (
  `id` int(11) NOT NULL,
  `cpf_usuario` varchar(80) NOT NULL,
  `placa` varchar(10) NOT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `ano` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `veiculo`
--

INSERT INTO `veiculo` (`id`, `cpf_usuario`, `placa`, `modelo`, `ano`) VALUES
(6, '222.222.222-22', 'ABC-1234', 'Fiat Marea', 2002);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `item_servico`
--
ALTER TABLE `item_servico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pedido` (`id_pedido`);

--
-- Índices de tabela `pedido_servico`
--
ALTER TABLE `pedido_servico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_veiculo` (`id_veiculo`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf_usuario` (`cpf_usuario`);

--
-- Índices de tabela `veiculo`
--
ALTER TABLE `veiculo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cpf_usuario` (`cpf_usuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `item_servico`
--
ALTER TABLE `item_servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `pedido_servico`
--
ALTER TABLE `pedido_servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `veiculo`
--
ALTER TABLE `veiculo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `item_servico`
--
ALTER TABLE `item_servico`
  ADD CONSTRAINT `item_servico_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido_servico` (`id`);

--
-- Restrições para tabelas `pedido_servico`
--
ALTER TABLE `pedido_servico`
  ADD CONSTRAINT `pedido_servico_ibfk_1` FOREIGN KEY (`id_veiculo`) REFERENCES `veiculo` (`id`);

--
-- Restrições para tabelas `veiculo`
--
ALTER TABLE `veiculo`
  ADD CONSTRAINT `veiculo_ibfk_1` FOREIGN KEY (`cpf_usuario`) REFERENCES `usuarios` (`cpf_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
