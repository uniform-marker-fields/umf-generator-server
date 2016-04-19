SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `pmgen`
--

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE IF NOT EXISTS `images` (
  `img_id` int(11) NOT NULL AUTO_INCREMENT,
  `img_extension` varchar(4) NOT NULL,
  `img_name` varchar(127) NOT NULL DEFAULT '',
  PRIMARY KEY (`img_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `marker_gid`
--

CREATE TABLE IF NOT EXISTS `marker_gid` (
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(64) NOT NULL,
  `state` tinyint(4) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_assigned` timestamp NULL DEFAULT NULL,
  `testing` tinyint(4) NOT NULL DEFAULT '0',
  `force_continue` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=183 ;

-- --------------------------------------------------------

--
-- Table structure for table `markers_ready`
--

CREATE TABLE IF NOT EXISTS `markers_ready` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL DEFAULT '-1',
  `short_name` varchar(64) DEFAULT '',
  `type` smallint(8) NOT NULL DEFAULT '0',
  `width` int(11) NOT NULL DEFAULT '15',
  `height` int(11) NOT NULL DEFAULT '15',
  `kernel` int(11) NOT NULL DEFAULT '4',
  `data` mediumtext NOT NULL,
  `colors` text NOT NULL,
  `runtime` double NOT NULL DEFAULT '0',
  `kernel_type` int(32) NOT NULL,
  `img_id` int(11) NOT NULL DEFAULT '0',
  `img_alg` varchar(127) NOT NULL DEFAULT '',
  `module_type` int(11) NOT NULL DEFAULT '0',
  `threshold_equal` int(11) NOT NULL DEFAULT '0',
  `cost_neighbors` text NOT NULL,
  `cost_similarity` float NOT NULL DEFAULT '1',
  `img_conv` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=419 ;

-- --------------------------------------------------------

--
-- Table structure for table `process_queue`
--

CREATE TABLE IF NOT EXISTS `process_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL DEFAULT '-1',
  `type` smallint(8) NOT NULL DEFAULT '0',
  `width` int(11) NOT NULL DEFAULT '15',
  `height` int(11) NOT NULL DEFAULT '15',
  `kernel` int(11) NOT NULL DEFAULT '4',
  `conflicts` int(11) NOT NULL DEFAULT '100',
  `last_assigned` timestamp NULL DEFAULT NULL,
  `boost` tinyint(4) NOT NULL DEFAULT '0',
  `cost` float NOT NULL DEFAULT '0',
  `colors` text NOT NULL,
  `kernel_type` int(32) NOT NULL,
  `img_id` int(11) NOT NULL DEFAULT '0',
  `img_alg` varchar(127) NOT NULL DEFAULT '',
  `module_type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10341 ;

-- --------------------------------------------------------

--
-- Table structure for table `queue_data`
--

CREATE TABLE IF NOT EXISTS `queue_data` (
  `id` int(11) NOT NULL DEFAULT '0',
  `data` mediumtext NOT NULL,
  `runtime` double NOT NULL DEFAULT '0',
  `threshold_equal` int(11) NOT NULL DEFAULT '0',
  `cost_neighbors` text NOT NULL,
  `cost_similarity` float NOT NULL DEFAULT '1',
  `img_conv` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
