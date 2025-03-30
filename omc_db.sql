-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2025 at 04:30 AM
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
-- Database: `omc_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bom`
--

CREATE TABLE `bom` (
  `id` int(11) NOT NULL,
  `project_name` varchar(20) NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `material_type` int(11) NOT NULL,
  `length` decimal(10,2) NOT NULL,
  `width` decimal(10,2) NOT NULL,
  `thickness` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `materials` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`materials`)),
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bom`
--

INSERT INTO `bom` (`id`, `project_name`, `material_name`, `material_type`, `length`, `width`, `thickness`, `quantity`, `material_id`, `materials`, `project_id`) VALUES
(1, '', '', 0, 0.00, 0.00, 0.00, 0, 0, '[\"5\",\"5\"]', 0),
(2, '', '', 0, 0.00, 0.00, 0.00, 0, 0, '[\"100\",\"100\"]', 0),
(3, '', '', 0, 0.00, 0.00, 0.00, 0, 0, '[\"118\",\"118\"]', 0),
(4, '', '', 0, 0.00, 0.00, 0.00, 0, 0, '[{\"material_id\":\"5\",\"material_name\":\"2x2 pine\",\"length\":\"96.00\",\"width\":\"1.50\",\"thickness\":\"1.50\",\"quantity\":\"1\"},{\"material_id\":\"5\",\"material_name\":null,\"length\":null,\"width\":null,\"thickness\":null,\"quantity\":null}]', 0),
(5, '', '', 0, 0.00, 0.00, 0.00, 0, 0, '[{\"material_id\":\"118\",\"material_name\":\"Birch Plywood\",\"length\":\"96.00\",\"width\":\"48.00\",\"thickness\":\"0.75\",\"quantity\":\"1\"},{\"material_id\":\"118\",\"material_name\":null,\"length\":null,\"width\":null,\"thickness\":null,\"quantity\":null}]', 0),
(6, '', '', 0, 0.00, 0.00, 0.00, 0, 0, '[{\"material_id\":\"100\",\"material_name\":\"2x4x10\' Ground Contact Green Pressure Treated\",\"length\":\"96.00\",\"width\":\"3.50\",\"thickness\":\"1.50\",\"quantity\":\"1\"},{\"material_id\":\"100\",\"material_name\":null,\"length\":null,\"width\":null,\"thickness\":null,\"quantity\":null}]', 0),
(7, '', '2x2 pine', 0, 96.00, 1.50, 1.50, 1, 0, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `address` varchar(20) NOT NULL,
  `city` varchar(12) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` int(5) NOT NULL,
  `phone` int(10) NOT NULL,
  `email` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `address`, `city`, `state`, `zip`, `phone`, `email`) VALUES
(1, 'Jack B', '244 Kingfisher Bay D', 'Demopolis ', 'Al', 36752, 2147483647, 'brawley.jv@gmail.com'),
(2, 'Jack Brawley', '8048 hwy 125n', 'rogersville', 'Mi', 65742, 2147483647, 'brawley.jv@gmail.com'),
(3, 'Jack Brawley', '8048 hwy 125n', 'rogersville', 'Mi', 65742, 2147483647, 'brawley.jv@gmail.com'),
(4, 'Jack Brawley', '8048 hwy 125n', 'rogersville', 'Mi', 65742, 2147483647, 'brawley.jv@gmail.com'),
(5, 'Jack Brawley', '8048 hwy 125n', 'rogersville', 'Mi', 65742, 2147483647, 'brawley.jv@gmail.com'),
(6, 'Jack Brawley', '8048 hwy 125n', 'rogersville', 'Mi', 65742, 2147483647, 'brawley.jv@gmail.com'),
(7, 'Jack Brawley', '8048 hwy 125n', 'rogersville', 'Mi', 65742, 2147483647, 'brawley.jv@gmail.com'),
(8, 'Jack Brawley', '8048 hwy 125n', 'rogersville', 'Mi', 65742, 2147483647, 'brawley.jv@gmail.com'),
(9, 'Jack Brawley', '8048 hwy 125n', 'rogersville', 'Mi', 65742, 2147483647, 'brawley.jv@gmail.com'),
(10, 'Jack Brawley', '8048 hwy 125n', 'rogersville', 'Mi', 65742, 2147483647, 'brawley.jv@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(4) NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `Length` decimal(10,2) NOT NULL,
  `Width` decimal(10,2) NOT NULL,
  `Thickness` decimal(10,2) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Quantity_on_Hand` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `vendor` varchar(255) NOT NULL,
  `Item_no` varchar(11) NOT NULL,
  `item_url` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `package_qnty` int(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `material_name`, `Length`, `Width`, `Thickness`, `Price`, `Quantity_on_Hand`, `type`, `vendor`, `Item_no`, `item_url`, `image_url`, `package_qnty`) VALUES
(1, '2x2 pine', 96.00, 1.50, 1.50, 15.49, 1, 'Lumber', 'Menards', 'SKU: 103210', 'https://www.menards.com/main/building-materials/lumber-boards/boards/2-x-2-select-pine-board/1032104/p-1444444785491-c-13115.htm', 'https://cdn.menardc.com/main/items/media/TAUPO001/ProductXLarge/103-2100_P_.jpg', 0),
(2, '2x4x10\' Ground Contact Green Pressure Treated', 96.00, 3.50, 1.50, 7.18, 2, 'Lumber', '4', 'SKU: 111082', 'https://www.menards.com/main/building-materials/lumber-boards/dimensional-lumber/ac2-reg-2-x-4-ground-contact-green-pressure-treated-lumber/1110821/p-1444422152055-c-13125.htm?exp=false', 'https://cdn.menardc.com/main/items/media/99998/ProductXLarge/1110818_2x4Treated-1.jpg', 0),
(3, 'Leather Key Fob Kit', 0.00, 0.00, 0.00, 0.86, 10, 'Plywood', '5', 'T-0061J', 'https://www.amazon.com/dp/B0CC2MWYRB?ref=ppx_yo2ov_dt_b_fed_asin_title&th=1', 'https://m.media-amazon.com/images/I/71t2xocOXkL._AC_SY355_.jpg', 0),
(4, 'Stainless Steel Credit Card Size Beer Bottle Opener', 3.24, 2.12, 0.04, 1.00, 1, 'Finished Product', 'Amazon', 'AGUS138', 'https://www.amazon.com/dp/B07QKXS65K/?coliid=I755543GW6977&colid=SMPWOWI88Y7Y&ref_=list_c_wl_lv_ov_lig_dp_it&th=1', 'https://m.media-amazon.com/images/I/51CPxypy8yL._AC_SX569_.jpg', 0),
(5, '2 x 4 x 8\' Red Cedar S4S Lumber', 96.00, 3.50, 1.50, 11.99, 0, 'Lumber', '4', 'SKU: 107275', 'https://www.menards.com/main/building-materials/lumber-boards/dimensional-lumber/2-x-4-red-cedar-s4s-lumber/1072752/p-1444422743271-c-13125.htm', 'https://cdn.menardc.com/main/items/media/99998/ProductXLarge/107-2752_P_1_3122.jpg', 0),
(6, '1 x 4 x 8\' Red Cedar Board', 96.00, 3.50, 0.81, 9.22, 0, 'Lumber', '4', 'SKU: 107134', 'https://www.menards.com/main/building-materials/lumber-boards/boards/1-x-4-red-cedar-board/1071342/p-1444422489746-c-13115.htm?exp=true', 'https://cdn.menardc.com/main/items/media/99998/ProductXLarge/107-1342_P_1_3122.jpg', 0),
(7, 'Bamboo Cutting Board', 11.00, 5.10, 0.60, 4.42, 0, 'Finished Product', '5', 'B0BQ78MRBB', 'https://www.amazon.com/dp/B0BQ78MRBB/?coliid=I2AW6L5RY0GZC0&colid=SMPWOWI88Y7Y&ref_=list_c_wl_lv_ov_lig_dp_it&th=1', 'https://m.media-amazon.com/images/I/81tvkHHD5TL._AC_SX569_.jpg', 0),
(8, 'Birch Plywood', 96.00, 48.00, 0.75, 69.99, 1, 'Plywood', '4', '1251699', 'https://www.menards.com/main/building-materials/panel-products/hardwood-panels/4-x-8-birch-wood-veneer-core-plywood/1251699/p-1444441906195-c-13334.htm?exp=false', 'https://cdn.menardc.com/main/items/media/SHELT004/ProductXLarge/1251699_P_ALT1_091922.jpg', 1),
(9, 'Birch Plywood', 96.00, 48.00, 0.25, 29.99, 0, 'Plywood', '4', 'SKU: 125404', 'https://www.menards.com/main/building-materials/panel-products/hardwood-panels/4-x-8-birch-wood-veneer-core-plywood/1251699/building-materials/panel-products/handi-panels/4-x-4-birch-plywood-handi-panel/1254046/p-1444441905350-c-13337.htm', 'https://cdn.menardc.com/main/items/media/SHELT004/ProductXLarge/BirchGrainClose.jpg', 1),
(10, 'Natural Maple', 96.00, 48.00, 0.75, 89.43, 0, 'Plywood', '4', 'SKU: 125185', 'https://www.menards.com/main/building-materials/panel-products/hardwood-panels/3-4-x-4-x-8-b2-natural-maple-wood-veneer-core-plywood/34x4x8b2maple/p-1444445023585-c-13334.htm', 'https://cdn.menardc.com/main/items/media/TIMBE002/ProductXLarge/125-1850_P_A1_101223.jpg', 1),
(11, 'Natural Maple-1', 96.00, 48.00, 0.25, 42.99, 0, 'Plywood', '4', 'SKU: 125184', 'https://www.menards.com/main/building-materials/panel-products/hardwood-panels/1-4-x-4-x-8-b4-natural-maple-mdf-core-plywood/14x4x8b4maplemdf/p-1444445023123-c-13334.htm?exp=false', 'https://cdn.menardc.com/main/items/media/TIMBE002/ProductXLarge/1251840-1.jpg', 1),
(13, 'Padauk Veneer Set - 3pcs', 12.00, 12.00, 0.02, 6.33, 0, 'veneer', 'Wood Workers Source', 'ven1212-12', 'https://www.woodworkerssource.com/raw-wood-veneer-packs/padauk-veneer-segment.html', 'https://www.woodworkerssource.com/mm5/graphics/veneer/sauers/Veneer-12x12-Padauk_1000x667.jpg', 1),
(14, 'MDO Plywood 3/4 x 4 x 8 2 Sided', 96.00, 48.00, 0.75, 99.99, 0, 'Plywood', 'Menards', 'SKU: 125505', 'https://www.menards.com/main/building-materials/panel-products/mdf-mdo-panels/3-4-x-4-x-8-2-sided-mdo-plywood/1255058/p-1444452506069-c-13338.htm?exp=false', 'https://cdn.menardc.com/main/items/media/ROSEB001/ProductXLarge/125-5003.jpg', 1),
(15, 'High Density Expanded Polystyrene Foam', 96.00, 48.00, 2.00, 39.88, 0, 'Foam', 'Menards', 'SKU: 163213', 'https://www.menards.com/main/building-materials/insulation/foam-board-insulation/r-8-8-high-density-expanded-polystyrene-2-x-4-x-8-foam-board-insulation/16312130/p-105477663874-c-5779.htm?exp=false', 'https://cdn.menardc.com/main/items/media/INSUL004/ProductXLarge/IF-13415Insulfoam2_25PSIBoardExtensionImage_1000pxx1000px_300dpi.jpg', 1),
(16, '1 x 10 x 8\' Quality Pine Board', 96.00, 9.25, 0.75, 14.38, 0, 'Lumber', '4', 'SKU: 103375', 'https://cdn.menardc.com/main/items/media/99998/ProductXLarge/103-3747_P_2_071322.jpg', 'https://cdn.menardc.com/main/items/media/99998/ProductXLarge/103-3747_P_2_071322.jpg', 1),
(17, 'Leather Handle', 7.56, 1.46, 0.00, 6.89, 0, 'Plywood', '5', '72938871578', 'https://www.amazon.com/MTQY-Leather-Handles-Mounting-Wardrobe/dp/B09KLNKDK1/ref=sr_1_7?crid=3J86KXMRLLQ6J&dib=eyJ2IjoiMSJ9.b58iWRCugEuV7l4meXdOLwJoo_03UDjyAJ9bIh5VaDwDnNwOZVx_pnIGnoAhhtQlZcyzmi7b7mMCfIuPWJkv6mGtqwHKaWgE7LxJeAGHrwNtGsIjlD2EQaMGrDDnGcrD1j01', 'https://m.media-amazon.com/images/I/61b+tYvKE4L._AC_SX569_.jpg', 1),
(18, 'Solid Bronze Padlock Hasp', 3.50, 0.00, 0.00, 15.98, 0, 'Finished Product', 'Amazon', '08414721971', 'https://www.amazon.com/Runningfish-Padlock-Antique-Furniture-Drawers/dp/B07TYXZYTW/ref=sr_1_27?crid=JJ2CXRYH19LX&dib=eyJ2IjoiMSJ9.RqaG8i5BakyhNV3iUMlRu_r1TyaGuNszaRFf54a7sNjpDD4KMzLfO_n7VrwUIYpdE7ALZ5PFDGYro8RMrrNzWL4x9hzhFCNDr1YOZboOrF_6isupUKwUsl-CStu14', 'https://m.media-amazon.com/images/S/aplus-media-library-service-media/bcfb822e-dd5f-482e-9efe-3e9bb5bcb61b.__CR0,0,970,600_PT0_SX970_V1___.png', 1),
(19, 'Antique Pure Brass Hinges', 3.35, 0.00, 0.00, 8.99, 0, 'Plywood', '5', '74172251895', 'https://www.amazon.com/Tiazza-Antique-Furniture-Decorative-Hardware/dp/B07RJH33NP?source=ps-sl-shoppingads-lpcontext&ref_=fplfs&smid=AA0MAHPU0AXJ8&gQT=1&th=1', 'https://m.media-amazon.com/images/I/51G3eOm9oJL._AC_SX569_.jpg', 1),
(20, 'Shot Gun Shell Button Ends', 0.00, 0.00, 0.00, 1.95, 0, 'Finished Product', 'Etsy', '', 'https://www.etsy.com/listing/205274142/12-gauge-shotgun-shell-cut-brass-ends?ga_order=most_relevant&ga_search_type=all&ga_view_type=gallery&ga_search_query=shotgun+shell+ends&ref=sr_gallery-1-2&content_source=9f15f12c1baf4ac9667296480b1bb16e159074fe%253A2', 'https://i.etsystatic.com/6935195/r/il/a43e27/653245492/il_794xN.653245492_hhdi.jpg', 1),
(21, 'Name Plates Laser', 3.00, 1.00, 0.00, 2.00, 0, 'Finished Product', 'Amazon', 'Lx0073', 'https://www.amazon.com/Lxmxgk-Engraved-Plaques-Personalized-Engraving/dp/B0CZSQZXHJ/ref=sr_1_11?crid=3UHAX5C1KDRUG&dib=eyJ2IjoiMSJ9.ifBH6SPkP8PtuZfSrxYmyVy4iHZ26rEKIGNJ1dBwymycC3AmntO1xC0BFR4NhEM8VA_0xbI6Kmwn6K0xXYiPlvmynSerBWZE0giyu7j-L8zK7HbKkT4oDNH2N_z', 'https://m.media-amazon.com/images/I/61Jv5D1Nr8L._AC_SX569_.jpg', 1),
(22, 'Cedar Fence Pickets', 48.00, 5.50, 0.63, 2.98, 0, 'Lumber', '4', 'SKU: 173125', 'https://www.menards.com/main/building-materials/fencing/wood-fencing/1-x-6-cedar-dog-ear-fence-picket/1731252/p-1444442567726-c-5774.htm?exp=false', 'https://cdn.menardc.com/main/items/media/SIERR001/ProductXLarge/1731257_P_ALT1.jpg', 1),
(23, '1/4 x 4 x 8 Sanded Utility Plywood', 96.00, 48.00, 0.19, 21.99, 0, 'Plywood', '4', '1252006', 'https://www.menards.com/main/building-materials/panel-products/hardwood-panels/4-x-8-sanded-utility-plywood/1252006/p-1444441906578-c-13334.htm?exp=false', 'https://cdn.menardc.com/main/items/media/SHELT004/ProductXLarge/1252006_P_ALT1_091922.jpg', 1),
(24, '1/2 x 4 x 8 Sanded Utility Plywood', 96.00, 48.00, 0.50, 43.99, 0, 'Plywood', 'Menards', '1252015', 'https://www.menards.com/main/building-materials/panel-products/hardwood-panels/4-x-8-sanded-utility-plywood/1252015/p-1444441905962-c-13334.htm', 'https://cdn.menardc.com/main/items/media/SHELT004/ProductXLarge/1252006face.jpg', 1),
(25, '1 x 24 x 4\' Red Oak Board', 48.00, 24.00, 0.75, 71.99, 0, 'Lumber', '<br /><b>Deprecated</b>:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in <b>C:\\xampp\\htdocs\\Views\\materials\\edit_material.php</b> on line <b>112</b><br />', '1043769', 'https://www.menards.com/main/building-materials/lumber-boards/hardwood-lumber-boards/mastercraft-reg-1-x-24-red-oak-board/1043769/p-1568183359236-c-10067.htm?exp=false', 'https://cdn.menardc.com/main/items/media/TOMSC003/ProductXLarge/104-3768-2_042723.jpg', 1),
(26, '1 x 8 x 8\' Red Oak Board', 96.00, 7.25, 0.75, 43.44, 0, 'Lumber', 'Menards', '1043257', 'https://www.menards.com/main/building-materials/lumber-boards/boards/mastercraft-reg-1-x-8-red-oak-board/1043257/p-1444422756395-c-13115.htm?exp=false', 'https://cdn.menardc.com/main/items/media/99998/ProductXLarge/104-3118-2_042723.jpg', 1),
(27, '4\' Wood Lath Bundle (30 Pieces)', 48.00, 1.50, 0.25, 12.99, 0, 'Lumber', 'Menards', '1022946', 'https://www.menards.com/main/building-materials/lumber-boards/boards/4-wood-lath-bundle-30-pieces/1022946/p-1642874315744723-c-13115.htm?exp=false', 'https://cdn.menardc.com/main/items/media/GREAT067/ProductXLarge/102-2946_P_R1_060324.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `material_types`
--

CREATE TABLE `material_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_types`
--

INSERT INTO `material_types` (`id`, `type_name`) VALUES
(1, 'Plywood'),
(2, 'Lumber'),
(3, 'Acrylic'),
(4, 'Hardware'),
(5, 'Paint/Stain/Finishes'),
(6, 'Consumables'),
(7, 'Veneer');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_name` varchar(255) NOT NULL,
  `design_date` date NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `laser_time` int(4) NOT NULL,
  `labor_hours` int(4) NOT NULL,
  `file_upload` varchar(255) NOT NULL,
  `design_file` varchar(255) NOT NULL,
  `image_upload` varchar(255) NOT NULL,
  `due_date` date NOT NULL,
  `project_description` text NOT NULL,
  `router_time` int(4) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_name`, `design_date`, `customer_name`, `laser_time`, `labor_hours`, `file_upload`, `design_file`, `image_upload`, `due_date`, `project_description`, `router_time`, `id`) VALUES
('Ammo Box', '2025-01-06', 'OMC', 6, 1, 'ammo box burn.lbrn2,code.gcode', 'ammo box burn.lbrn2,ammo box.crv3d', '1.bmp', '0000-00-00', 'Design for a ammo box.', 82, 1),
('Ammo Boxes', '2025-02-06', 'OMC', 0, 1, 'CODE_1-7-Pocket 1.gcode', '', '1.bmp', '0000-00-00', 'nested 3 boxes on 4x8 sheet of 1/4 ply', 110, 2),
('Desk Organizer', '2024-07-30', 'OMC', 64, 0, 'CODE_1-Pocket 1.gcode,CODE_2-3-Profile 1.gcode', 'organizer.crv3d', 'SAMPLE.jpg', '0000-00-00', 'Desk Organizer made from Birch ply .25', 62, 3),
('Horse in Bowl', '2024-01-04', 'OMC', 0, 0, 'horse_bowl_1-2-.25 ball roughing.gcode,horse_bowl_3-5-.25 V Grooves.gcode,horse_bowl_6-.25 End Mill Cut Out.gcode', '0', 'image3.bmp', '0000-00-00', '7x7 inch wood of choice cut into a bowl with a carved horse in the bottom. ', 139, 4),
('Ice Chest', '2024-09-10', 'OMC', 0, 1, 'CODE_1-Cut out.gcode,CODE_2-3D Roughing DEER.gcode,CODE_3-3D Finish DEER.gcode,CODE_4-Pocket.gcode', '', '1.bmp', '0000-00-00', 'Ice chest cut out', 390, 5),
('Leather Moonshine Keychain', '2025-01-10', 'OMC', 0, 1, 'MOONSHINE JUG.lbrn2,Moonshine keychain.lbrn2', '0', '1.png', '0000-00-00', 'Leather keychain fob with moonshine jug laser engraved', 8, 6),
('Marine Corp Carved', '2025-02-02', 'OMC', 0, 0, 'code_1-3D Roughing 1.gcode,code_2-3D Finish 1.gcode,code_3-3D Finish 2.gcode', '', '1.bmp', '0000-00-00', 'Marine Corp Emblem carved on Cedar or other suitable material minimum 6.25x5', 113, 7),
('Memphis Bridge', '2024-08-04', 'OMC', 6, 30, 'Memphis Bridge 6x6 tile.lbrn2', '0', '1.png', '0000-00-00', 'Sketch image of the Memphis Bridge great for 6x6 or 4x4 tiles', 0, 8),
('Moonshine Keychain', '2025-01-05', 'OMC', 1, 1, 'MOONSHINE JUG.lbrn2', '0', '1.png', '0000-00-00', 'Laser engraving Missouri Moonshine jug on Amazon Keychains', 0, 9),
('Spice Rack', '2025-02-16', 'OMC', 48, 48, '', 'sheet 1.lbrn2,sheet 2.lbrn2,sheet 3.lbrn2,sheet 4-5.lbrn2,sheet 6.lbrn2,sheet 7.lbrn2', '1.bmp', '0000-00-00', 'Designed for .22 Material to be cut on Blue.', 0, 11),
('Test Project With BOM', '2025-02-22', 'Jack', 5, 1, 'code_1-Pocket 1 [Clear 1].gcode,code_2-Pocket 1.gcode', 'Job Analysis.ods,logo 2.svg,plaque 1b.crv3d,plaque_Summary.html', '1A.bmp,1w.png,2A.bmp,3A.bmp', '2025-03-28', 'Test Project 2', 10, 12),
('GFL Front Load FU Biden', '2025-03-29', 'Tim', 31, 0, 'GFL FRONTLOAD.lbrn2', 'GFL FRONTLOAD.lbrn2', '2.png', '0000-00-00', 'Front-load trash truck with finger and Biden getting run over', 0, 18);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_slogan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `company_name`, `company_slogan`) VALUES
(1, 'OMC', 'OMC some crappy slogan goes here');

-- --------------------------------------------------------

--
-- Table structure for table `setup`
--

CREATE TABLE `setup` (
  `id` int(11) NOT NULL,
  `mill_rate` decimal(10,2) NOT NULL,
  `laser_rate` decimal(10,2) NOT NULL,
  `bit_change_rate` decimal(10,2) NOT NULL,
  `customize_rate` decimal(10,2) NOT NULL,
  `overhead_rate` decimal(10,2) NOT NULL,
  `labor_rate` decimal(10,2) NOT NULL,
  `sqf_milling_rate` decimal(10,2) NOT NULL,
  `packaging_rate` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `setup`
--

INSERT INTO `setup` (`id`, `mill_rate`, `laser_rate`, `bit_change_rate`, `customize_rate`, `overhead_rate`, `labor_rate`, `sqf_milling_rate`, `packaging_rate`, `created_at`, `updated_at`) VALUES
(1, 0.85, 0.50, 5.00, 5.00, 10.00, 25.00, 32.00, 20.00, '2025-02-21 03:16:57', '2025-03-27 15:20:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `user_type` varchar(10) NOT NULL,
  `date_of_hire` date NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `position`, `user_type`, `date_of_hire`, `password`) VALUES
(6, 'Jack Brawley', '9853527554', 'omc_user', 'admin', '2020-09-22', '$2y$10$lYK9cUaacU84QYGSWXS/NeY9MXR64dBg7VxxERdamM7k8O7J/iF76'),
(7, 'Jack', '9853527554', 'omc_user', 'admin', '2025-01-21', '$2y$10$gzPgtHBxLtAtwULWgNReye8VAL5CiFbyjtIBOns6oREUywR0Vsvda'),
(13, 'jvb', '9853527554', 'owner', 'admin', '2025-03-25', '$2y$10$Iavcdiqzc3M19WBFfPLWkOVzs94jRUbLaC2iOq3QDzUsCfx7R.X9.');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `Vendor` varchar(255) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Web_Address` varchar(255) DEFAULT NULL,
  `Mailing_Address` varchar(255) DEFAULT NULL,
  `Email_Address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `Vendor`, `Phone`, `Web_Address`, `Mailing_Address`, `Email_Address`) VALUES
(4, 'Menards', '', 'https://www.menards.com/main/home.html', '', ''),
(5, 'Amazon', '', 'https://www.amazon.com/', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `material_types`
--
ALTER TABLE `material_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_name` (`project_name`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `setup`
--
ALTER TABLE `setup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `material_types`
--
ALTER TABLE `material_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `setup`
--
ALTER TABLE `setup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bom`
--
ALTER TABLE `bom`
  ADD CONSTRAINT `bom_ibfk_2` FOREIGN KEY (`material_type`) REFERENCES `material_types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
