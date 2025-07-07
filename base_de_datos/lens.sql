-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-06-2025 a las 02:51:49
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `lens`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `id_comentario` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_pelicula` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `valoracion` int(11) NOT NULL,
  `fecha_comentario` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comentarios`
--

INSERT INTO `comentarios` (`id_comentario`, `id_usuario`, `id_pelicula`, `comentario`, `valoracion`, `fecha_comentario`) VALUES
(1, 2, 283, '¡Me encantó la película! Muy recomendable.', 5, '2024-06-25'),
(2, 3, 284, 'Buena trama, pero esperaba más del final.', 3, '2024-06-25'),
(3, 4, 285, 'Ideal para ver en familia, los niños la disfrutaron mucho.', 4, '2024-06-26'),
(4, 5, 286, 'Demasiado larga, pero los efectos especiales son geniales.', 4, '2024-06-26'),
(5, 2, 287, 'No me gustó, la historia es muy predecible.', 2, '2024-06-27'),
(6, 6, 288, 'La animación es espectacular, la recomiendo.', 5, '2024-06-27'),
(7, 7, 289, 'Un poco lenta al principio, pero mejora mucho.', 3, '2024-06-28'),
(8, 8, 290, '¡Divertidísima! Fui con amigos y la pasamos genial.', 5, '2024-06-28'),
(9, 9, 291, 'Me asusté bastante, muy buena para fans del terror.', 4, '2024-06-29'),
(10, 10, 292, 'El protagonista es genial, pero el final es flojo.', 3, '2024-06-29'),
(11, 3, 293, 'Drama intenso, me hizo reflexionar.', 4, '2024-06-30'),
(12, 4, 294, 'Perfecta para ver en familia, muy divertida.', 5, '2024-06-30'),
(13, 5, 295, 'No la volvería a ver, no es mi estilo.', 2, '2024-07-01'),
(14, 6, 296, 'Acción sin parar, la sala aplaudió al final.', 5, '2024-07-01'),
(15, 7, 297, 'Un clásico, nunca decepciona.', 5, '2024-07-01'),
(16, 8, 283, 'La música es muy buena, gran banda sonora.', 4, '2024-07-01'),
(17, 9, 284, 'Los efectos visuales son impresionantes.', 5, '2024-07-01'),
(18, 10, 285, 'La historia es original, me gustó mucho.', 4, '2024-07-01'),
(19, 2, 286, 'No me convenció el villano.', 2, '2024-07-01'),
(20, 3, 287, 'Muy entretenida, la recomiendo.', 4, '2024-07-01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entradas`
--

CREATE TABLE `entradas` (
  `id_entrada` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_pelicula` int(11) NOT NULL,
  `id_funcion` int(11) NOT NULL,
  `fila` varchar(1) NOT NULL,
  `numero_asiento` int(11) NOT NULL,
  `fecha_compra` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `entradas`
--

INSERT INTO `entradas` (`id_entrada`, `id_usuario`, `id_pelicula`, `id_funcion`, `fila`, `numero_asiento`, `fecha_compra`) VALUES
(1, 2, 283, 1, 'B', 12, '2024-06-25 11:00:00'),
(2, 3, 284, 2, 'C', 8, '2024-06-25 12:00:00'),
(3, 4, 285, 3, 'A', 5, '2024-06-26 13:00:00'),
(4, 5, 286, 4, 'D', 20, '2024-06-26 14:00:00'),
(5, 2, 287, 5, 'A', 1, '2024-06-27 15:00:00'),
(6, 6, 288, 6, 'B', 15, '2024-06-27 16:00:00'),
(7, 7, 289, 7, 'C', 10, '2024-06-28 17:00:00'),
(8, 8, 290, 8, 'D', 22, '2024-06-28 18:00:00'),
(9, 9, 291, 9, 'A', 3, '2024-06-29 19:00:00'),
(10, 10, 292, 10, 'B', 7, '2024-06-29 20:00:00'),
(11, 3, 293, 11, 'C', 14, '2024-06-30 21:00:00'),
(12, 4, 294, 12, 'D', 25, '2024-06-30 22:00:00'),
(13, 5, 295, 13, 'A', 2, '2024-07-01 10:00:00'),
(14, 6, 296, 14, 'B', 18, '2024-07-01 11:00:00'),
(15, 7, 297, 15, 'C', 9, '2024-07-01 12:00:00'),
(16, 8, 283, 1, 'D', 30, '2024-07-01 13:00:00'),
(17, 9, 284, 2, 'A', 6, '2024-07-01 14:00:00'),
(18, 10, 285, 3, 'B', 11, '2024-07-01 15:00:00'),
(19, 2, 286, 4, 'C', 17, '2024-07-01 16:00:00'),
(20, 3, 287, 5, 'D', 21, '2024-07-01 17:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `funciones`
--

CREATE TABLE `funciones` (
  `id_funcion` int(11) NOT NULL,
  `id_pelicula` int(11) NOT NULL,
  `id_sala` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `precio` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `funciones`
--

INSERT INTO `funciones` (`id_funcion`, `id_pelicula`, `id_sala`, `fecha_hora`, `precio`) VALUES
(1, 283, 1, '2024-07-01 18:00:00', 120.00),
(2, 284, 2, '2024-07-01 20:30:00', 100.00),
(3, 285, 3, '2024-07-02 16:00:00', 90.00),
(4, 286, 1, '2024-07-02 21:00:00', 130.00),
(5, 287, 4, '2024-07-03 19:00:00', 200.00),
(6, 288, 5, '2024-07-03 22:00:00', 180.00),
(7, 289, 6, '2024-07-04 15:00:00', 80.00),
(8, 290, 1, '2024-07-04 18:00:00', 120.00),
(9, 291, 2, '2024-07-04 20:30:00', 100.00),
(10, 292, 3, '2024-07-05 16:00:00', 90.00),
(11, 293, 4, '2024-07-05 19:00:00', 200.00),
(12, 294, 5, '2024-07-05 22:00:00', 180.00),
(13, 295, 6, '2024-07-06 15:00:00', 80.00),
(14, 296, 1, '2024-07-06 18:00:00', 120.00),
(15, 297, 2, '2024-07-06 20:30:00', 100.00),
(24, 333, 6, '2025-06-26 22:49:00', 1200.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `peliculas`
--

CREATE TABLE `peliculas` (
  `id_pelicula` int(11) NOT NULL,
  `origen` enum('api','manual') NOT NULL,
  `tmdb_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `genero` varchar(100) DEFAULT NULL,
  `fecha_estreno` int(4) DEFAULT NULL,
  `duracion` int(11) DEFAULT NULL,
  `imagen_portada` varchar(500) DEFAULT NULL,
  `trailer_url` varchar(500) DEFAULT NULL,
  `actores` text DEFAULT NULL,
  `clasificacion` varchar(10) DEFAULT NULL,
  `estado` enum('activa','inactiva') NOT NULL DEFAULT 'activa',
  `fecha_agregada` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizada` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `peliculas`
--

INSERT INTO `peliculas` (`id_pelicula`, `origen`, `tmdb_id`, `titulo`, `descripcion`, `genero`, `fecha_estreno`, `duracion`, `imagen_portada`, `trailer_url`, `actores`, `clasificacion`, `estado`, `fecha_agregada`, `fecha_actualizada`) VALUES
(283, 'api', 574475, 'Destino final: Lazos de sangre', 'Acosada por una violenta pesadilla recurrente, la estudiante universitaria Stefanie se dirige a casa para localizar a la única persona que podría ser capaz de romper el ciclo y salvar a su familia de la espeluznante muerte que inevitablemente les espera a todos.', 'Terror, Misterio', 2025, NULL, 'https://image.tmdb.org/t/p/w500/frNkbclQpexf3aUzZrnixF3t5Hw.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(284, 'api', 1311844, 'The Twisters', 'Decenas de tornados mortíferos convergen en el Medio Oeste, fusionándose en un mega tornado dispuesto a arrasar todo a lo largo de cientos de kilómetros.', 'Acción, Aventura, Drama', 2024, NULL, 'https://image.tmdb.org/t/p/w500/8OP3h80BzIDgmMNANVaYlQ6H4Oc.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(285, 'api', 552524, 'Lilo y Stitch', 'La conmovedora y divertidísima historia de una solitaria niña hawaiana y el extraterrestre fugitivo que la ayuda a reparar su desestructurada familia.', 'Familia, Ciencia ficción, Comedia, Aventura', 2025, NULL, 'https://image.tmdb.org/t/p/w500/tUae3mefrDVTgm5mRzqWnZK6fOP.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(286, 'api', 605722, 'Larga Distancia', 'Tras un aterrizaje forzoso, un ingeniero especializado en la perforación de asteroides acaba en un planeta alienígena para encontrar al único superviviente que se encuentra en aquel recóndito lugar. Para ello, deberá enfrentarse a extrañas criaturas.', 'Ciencia ficción, Comedia, Acción', 2024, NULL, 'https://image.tmdb.org/t/p/w500/reDAdPDDZs5diNTef6UNuRwHomW.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(287, 'api', 1442776, 'Guerra de mutantes', 'El vertido de aguas residuales de una fábrica da origen a un lagarto gigante mutante. Después del accidente, el jefe ordena reunir a un grupo de vecinos para intentar dar caza a la bestia. El mejor cazador de la aldea, Hu Tianming, y la zoóloga Zhao Xiaoye logran localizar el escondite del lagarto gigante basándose en sus hábitos biológicos.', 'Acción, Suspense, Terror', 2024, NULL, 'https://image.tmdb.org/t/p/w500/nimnvsGwgJBPnsRrmdlIDi2Yhbn.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(288, 'api', 1087192, 'Cómo entrenar a tu dragón', 'En la escarpada isla de Berk, donde vikingos y dragones han sido enemigos acérrimos durante generaciones, Hipo se desmarca desafiando siglos de tradición cuando entabla amistad con Desdentao, un temido dragón de la Furia Nocturna. Su insólito vínculo revela la verdadera naturaleza de los dragones y desafía los cimientos de la sociedad vikinga.', 'Fantasía, Familia, Acción', 2025, NULL, 'https://image.tmdb.org/t/p/w500/sGFO6VshDEQ9vX4tOSobLJpkNhq.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(289, 'api', 1181039, 'La tumba del rey maldito', 'Tres aventureros logran escapar del temible Pozo Fantasma de Jingjue, pero no salen ilesos: se infectan con una enfermedad mortal. Para salvarse, deben encontrar la mítica Perla de Haochen, escondida en la tumba del Rey Xian, que es la única capaz de romper la maldición de los ojos fantasmales. En su épica búsqueda, se enfrentan a trampas mortales en las tumbas antiguas.', 'Acción, Aventura, Terror', 2023, NULL, 'https://image.tmdb.org/t/p/w500/Ak5gLdMklxjPjKyUEUDK6P2wt2y.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(290, 'api', 803796, 'Las guerreras k-pop', 'Cuando no están llenando estadios, las superestrellas del K-pop Rumi, Mira y Zoey usan sus poderes secretos para proteger a sus fans de una amenaza sobrenatural.', 'Animación, Fantasía, Acción, Comedia, Música', 2025, NULL, 'https://image.tmdb.org/t/p/w500/swQRKmW7RLhncPYHvM0RHz8b7bT.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(291, 'api', 1100988, '28 años después', 'Años transcurridos tras los sucesos de \"28 semanas después\", el virus de la ira ha regresado y un grupo de supervivientes debe sobrevivir en un mundo asolado por hordas de infectados. Rodada con un iPhone 15 Pro Max y con la ayuda de numerosos accesorios especializados.', 'Terror, Suspense, Ciencia ficción', 2025, NULL, 'https://image.tmdb.org/t/p/w500/hvIqaDUC48dwfd7taKczB7909Qg.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(292, 'api', 1087891, 'Amateur', 'Charlie Heller es un brillante pero introvertido decodificador de la CIA que trabaja en una oficina en el sótano de la sede de Langley. Su vida cambia radicalmente cuando su esposa muere en un ataque terrorista en Londres. Cuando sus supervisores se niegan a tomar cartas en el asunto, él mismo toma las riendas y se embarca en un peligroso viaje por todo el mundo para localizar a los responsables. Su inteligencia puede ser el arma definitiva para llevar a cabo su venganza.', 'Suspense, Acción', 2025, NULL, 'https://image.tmdb.org/t/p/w500/9WPWF0AmklsKclrXeyKXbQ543ZA.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(293, 'api', 1426776, 'Harta', '¿Cuál será la gota que colme el vaso? Un día devastador lleva al límite a una madre soltera y trabajadora, y la empuja a cometer un impactante acto de desesperación.', 'Suspense, Drama, Crimen', 2025, NULL, 'https://image.tmdb.org/t/p/w500/eAf8Is1xX4gq4Jk14iFC1qhmT8R.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(294, 'api', 950387, 'Una película de Minecraft', 'Cuatro inadaptados se encuentran luchando con problemas ordinarios cuando de repente se ven arrastrados a través de un misterioso portal al Mundo Exterior: un extraño país de las maravillas cúbico que se nutre de la imaginación. Para volver a casa, tendrán que dominar este mundo mientras se embarcan en una búsqueda mágica con un inesperado experto artesano, Steve.', 'Familia, Comedia, Aventura, Fantasía', 2025, NULL, 'https://image.tmdb.org/t/p/w500/rZYYmjgyF5UP1AVsvhzzDOFLCwG.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(295, 'api', 1376434, 'Predator: Asesino de asesinos', 'Esta antología original de animación narra las aventuras de tres de los guerreros más fieros de la historia de la humanidad: una saqueadora vikinga que guía a su hijo en su sangrienta búsqueda de la venganza, un ninja del Japón feudal que se enfrenta a su hermano samurái en una brutal batalla por la sucesión y un piloto de la Segunda Guerra Mundial que surca los cielos para investigar una amenaza extraterrestre contra los Aliados.', 'Animación, Acción, Ciencia ficción', 2025, NULL, 'https://image.tmdb.org/t/p/w500/qHDsrBZJRx6ZCO4tocFh3gnbosU.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(296, 'api', 870028, 'El contable 2', 'Cuando un viejo conocido es asesinado, Wolff se ve obligado a resolver el caso. Al darse cuenta de que son necesarias medidas más extremas, Wolff recluta a su hermano Brax, distanciado y muy letal, para que le ayude. En colaboración con Marybeth Medina, descubren una conspiración mortal y se convierten en objetivo de una despiadada red de asesinos que no se detendrán ante nada para mantener sus secretos enterrados.', 'Misterio, Crimen, Suspense', 2025, NULL, 'https://image.tmdb.org/t/p/w500/jcV9YfjBRo6occxqeWtEiyWwMoG.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(297, 'api', 170, '28 días después', 'Londres es un cementerio. Las calles están ahora desiertas. Reina un silencio total. Tras la propagación de un virus que acabó con la mayor parte de la población, tuvo lugar la invasión de unos seres terroríficos. El virus se difundió, tras la incursión en un laboratorio, de un grupo de defensores de los derechos de los animales. Transmitido por la sangre, el virus produce efectos devastadores. En 28 días la epidemia se extiende por todo el país y sólo queda un puñado de supervivientes...', 'Terror, Suspense, Ciencia ficción', 2002, NULL, 'https://image.tmdb.org/t/p/w500/sIGsLU7hMDVKhGKsRFcFxUAtFyT.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(298, 'api', 1240475, 'Caza al mal', 'Narra la historia del agente de la policía de estupefacientes Huang Mingjin y el misterioso Wei Yunzhou. A través de medios y métodos completamente diferentes, en el proceso de cálculo y lucha entre ellos, desgarran la fachada de tranquilidad urbana, exponiendo la red subterránea de drogas tejida por la mano oculta \"Long Wang\".', 'Acción', 2024, NULL, 'https://image.tmdb.org/t/p/w500/n78hDmtawKOvJh3YLMcC5OJr2ER.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(299, 'api', 1233413, 'Los pecadores', 'Tratando de dejar atrás sus problemáticas vidas, dos hermanos gemelos regresan a su pueblo natal para empezar de nuevo, solo para descubrir que un mal aún mayor les espera para darles la bienvenida.', 'Terror, Fantasía, Suspense', 2025, NULL, 'https://image.tmdb.org/t/p/w500/jIVa5m9s7bKYdI0KH8wFw1qLxHl.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(300, 'api', 1090007, 'First Shift', 'Un oficial de policía de Nueva York junto con su compañera novata Angela tienen un día difícil mientras viven el trabajo peligroso y rutinario de ser policías en la ciudad.', 'Crimen, Suspense, Acción', 2024, NULL, 'https://image.tmdb.org/t/p/w500/ajsGI4JYaciPIe3gPgiJ3Vw5Vre.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:40', '2025-06-26 21:26:11'),
(301, 'api', 575265, 'Misión: Imposible - Sentencia final', 'El agente Ethan Hunt continúa su misión de impedir que Gabriel controle el tecnológicamente omnipotente programa de IA conocido como \"the Entity\".', 'Acción, Aventura, Suspense', 2025, NULL, 'https://image.tmdb.org/t/p/w500/haOjJGUV00dKlZaJWgjM1UD1cJV.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:41', '2025-06-26 21:26:12'),
(302, 'api', 911430, 'F1 la película', 'El mítico piloto Sonny Hayes vuelve de su retiro, persuadido para liderar un equipo de Fórmula 1 en apuros y guiar a su joven promesa, en busca de una nueva oportunidad de éxito.', 'Acción, Drama', 2025, NULL, 'https://image.tmdb.org/t/p/w500/6u4APbFBTcu3ieWBzFdyaGMLC9O.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:41', '2025-06-26 21:26:12'),
(303, 'api', 1180648, 'Mudbrick', 'Después de heredar una vieja casa de adobe en su pueblo natal en Europa del Este, un hombre regresa después de pasar toda su vida en Inglaterra, solo para descubrir que los habitantes esconden un oscuro secreto sobre el culto eslavo pagano y su propio pasado.', 'Terror, Drama', 2024, NULL, 'https://image.tmdb.org/t/p/w500/paVJNyxq5odaAHC9YlmNUlceQpu.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(304, 'api', 1239193, 'Deep Cover: Actores encubiertos', 'Kat es una profesora de teatro de improvisación que empieza a preguntarse si ha dejado pasar su oportunidad de triunfar. Cuando un policía encubierto le ofrece el papel de su vida, Kat recluta a dos de sus alumnos para infiltrarse en el mundo criminal londinense haciéndose pasar por peligrosos delincuentes.', 'Acción, Comedia, Crimen', 2025, NULL, 'https://image.tmdb.org/t/p/w500/dpLNAIU8AjAxw39fZfSDt2AiwMs.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(305, 'api', 1026542, 'Baby Blue', 'Un grupo de adolescentes se topan con la historia del ahora muerto asesino en serie Baby Blue y deciden que sería el tema perfecto para un vodcast de crímenes reales. Pero cuando comienzan a investigar, descubren rápidamente que su ola de asesinatos nunca se detuvo. Ahora están siendo atacados desde más allá de la tumba.', 'Terror, Comedia', 2023, NULL, 'https://image.tmdb.org/t/p/w500/z1BrCBJgG0VvehXHiIgxDrjNBhG.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(306, 'api', 1379587, 'Utopia', 'Un soldado investiga la desaparición de su esposa y se infiltra en las instalaciones creyendo que ha sido víctima de la trata. Descubre que se trata de un parque de fantasía futurista en el que realidad e ilusión se confunden, lo que complica su búsqueda de la verdad sobre el destino de la mujer.', 'Misterio, Suspense, Acción, Ciencia ficción', 2024, NULL, 'https://image.tmdb.org/t/p/w500/81PlfsAsb2wLY56UsbnUpMn80xh.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(307, 'api', 1289601, 'Lucha por tu vida', 'Un instructor de artes marciales se enfrenta a la desaparición de dos de sus alumnos, lo que le lleva a un enfrentamiento directo con un grupo internacional de traficantes de niños.', 'Acción, Drama, Suspense', 2024, NULL, 'https://image.tmdb.org/t/p/w500/8iZZyL7FyC4wYipwKemvuf58U3R.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(308, 'api', 1017163, 'Fuerza bruta: Castigo', 'Mientras investigan una aplicación de tráfico de drogas, Ma Seok-do y su equipo descubren una conexión entre el desarrollador de la aplicación, asesinado en Filipinas, y una organización de apuestas ilegales online. Mientras tanto, en Filipinas, Baek Chang-gi controla el mercado coreano de apuestas ilegales y siembra el terror con secuestros, agresiones y asesinatos. Su socio, el genio informático Chang Dong-cheol, planea un plan aún mayor en la propia Corea. Para poner fin a la creciente amenaza, el detective Ma amplía su operación proponiendo una alianza inesperada... Cuarta entrega de la exitosa franquicia de acción surcoreana \'The Roundup\'.', 'Acción, Crimen, Drama, Suspense, Comedia', 2024, NULL, 'https://image.tmdb.org/t/p/w500/h6DvCL25R61iRzN7qkUnFg6O33A.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(309, 'api', 1234821, 'Jurassic World: El renacer', 'Cinco años después de los eventos de \"Dominion\", sigue a Zora (Scarlett Johansson), que dirige un equipo para conseguir material genético de tres enormes dinosaurios. Cuando la operación se cruza con una familia, quedan varados en una isla donde hacen un siniestro descubrimiento.', 'Ciencia ficción, Aventura, Acción', 2025, NULL, 'https://image.tmdb.org/t/p/w500/k8ZmT0TpNGdUUUU4sWJyI8NL4uX.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(310, 'api', 541671, 'Bailarina', 'Eve Macarro es una asesina entrenada por la Ruska Roma desde su infancia, la misma organización criminal encargada del adiestramiento de John Wick. En esta violenta historia de venganza, Eve intentará por todos los medios averiguar quién está detrás del asesinato de su padre. En su lucha por conocer la verdad, tendrá que atenerse a las normas de la Alta Mesa y, por supuesto, a las del Hotel Continental, donde descubrirá que existen secretos ocultos sobre su pasado.', 'Acción, Suspense, Crimen', 2025, NULL, 'https://image.tmdb.org/t/p/w500/gQCrYmvCK7JCLXjCGTMRF5Lzr5c.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(311, 'api', 1197306, 'A Working Man', 'Levon Cade dejó atrás una condecorada carrera militar en las operaciones encubiertas para vivir una vida sencilla trabajando en la construcción. Pero cuando la hija de su jefe, que para él es como de la familia, es secuestrada por traficantes de personas, su búsqueda para traerla a casa descubre un mundo de corrupción mucho mayor de lo que jamás podría haber imaginado.', 'Acción, Crimen, Suspense', 2025, NULL, 'https://image.tmdb.org/t/p/w500/aG50Ad3UV8cCQWpNWSHHUqgKGNB.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(312, 'api', 1450599, 'K.O.', 'Un exluchador debe encontrar al hijo de un rival al que mató por accidente hace años y se enfrenta a una violenta banda criminal en Marsella.', 'Acción, Drama, Aventura', 2025, NULL, 'https://image.tmdb.org/t/p/w500/uTbQg4zQ0eGXlKl8imLu64LLJQX.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(313, 'api', 447273, 'Blancanieves', 'Tras la desaparición del benévolo Rey, la Reina Malvada dominó la otrora bella tierra con una vena cruel. La princesa Blancanieves huye del castillo cuando la Reina, celosa de su belleza interior, intenta matarla. En lo profundo del oscuro bosque, se topa con siete enanos mágicos y un joven bandido llamado Jonathan. Juntos, luchan por sobrevivir a la implacable persecución de la Reina y aspiran a recuperar el reino en el proceso.', 'Familia, Fantasía', 2025, NULL, 'https://image.tmdb.org/t/p/w500/sm91FNDF6OOKU4hT9BDW6EMoyDB.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(314, 'api', 666154, 'Kayara. La guerrera del imperio Inca', 'Una joven inca, apasionada por el trabajo de los mensajeros Chasqui, sueña con unirse a este grupo exclusivo de hombres encargados de llevar mensajes a lo largo del vasto Imperio Inca. Desafiando las estrictas normas de género y las tradiciones de su sociedad, la joven lucha contra las expectativas impuestas y enfrenta numerosos obstáculos en su búsqueda por cumplir su ambición. A pesar de las adversidades, su determinación y coraje la impulsan a romper barreras y a demostrar que su lugar está en el corazón del imperio.', 'Aventura, Animación, Familia', 2025, NULL, 'https://image.tmdb.org/t/p/w500/A1DSh29atQjLZCVCddMyGw1mbM8.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(315, 'api', 1315988, 'Mikaela', 'Víspera de Reyes. Una tormenta de nieve sin precedentes asola España. En medio del caos de una autopista colapsada, un grupo de atracadores aprovecha la oportunidad para asaltar un furgón blindado. A escasos metros se encuentra Leo (Antonio Resines), un policía en las últimas que no tiene nada que perder. Con la ayuda inesperada de una joven, juntos tratarán de evitar que la banda huya con el botín en una persecución a contrarreloj en medio del temporal.', 'Acción, Suspense', 2025, NULL, 'https://image.tmdb.org/t/p/w500/jm6lNbpmKcxX6M0pieaKhP6hs3w.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(316, 'api', 1022787, 'Elio', 'Cuenta la historia de Elio, un niño de 11 años, que lucha por encajar hasta que de repente es transportado por extraterrestres y es elegido para ser el embajador galáctico de la Tierra.', 'Familia, Comedia, Aventura, Animación, Ciencia ficción', 2025, NULL, 'https://image.tmdb.org/t/p/w500/fGjwHlv8bCjZypi2bHbbSmyIGMV.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(317, 'api', 1284120, 'La hermanastra fea', 'Elvira lucha contra su hermosa hermanastra en un reino donde la belleza reina suprema. Ella recurre a medidas extremas para cautivar al príncipe, en medio de una despiadada competición por la perfección física.', 'Terror, Comedia, Fantasía, Drama', 2025, NULL, 'https://image.tmdb.org/t/p/w500/mk4vGUy03tUCgJsOuwkYy877RYo.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(318, 'api', 986056, 'Thunderbolts*', 'Un grupo de supervillanos y antihéroes van en misiones para el gobierno. Basado en la serie de cómics del mismo nombre.', 'Acción, Ciencia ficción, Aventura', 2025, NULL, 'https://image.tmdb.org/t/p/w500/cGOBis1KNC8AkYMcOEw4lCycfD1.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:51', '2025-06-26 21:26:12'),
(319, 'api', 1137350, 'La trama fenicia', 'El magnate Zsa-zsa Korda es un rico empresario europeo que se ve envuelto en una trama de espionaje junto a su hija Liesl, una monja con la que mantiene una relación difícil y a la quiere dejar el negocio familiar.', 'Aventura, Comedia', 2025, NULL, 'https://image.tmdb.org/t/p/w500/rSjhapG9pfcJjKogZQPICHW0GwU.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(320, 'api', 10191, 'Cómo entrenar a tu dragón', 'Ambientada en el mítico mundo de los rudos vikingos y los dragones salvajes, y basada en el libro infantil de Cressida Cowell, esta comedia de acción narra la historia de Hipo, un vikingo adolescente que no encaja exactamente en la antiquísima reputación de su tribu como cazadores de dragones. El mundo de Hipo se trastoca al encontrar a un dragón que le desafía a él y a sus compañeros vikingos, a ver el mundo desde un punto de vista totalmente diferente.', 'Fantasía, Aventura, Animación, Familia', 2010, NULL, 'https://image.tmdb.org/t/p/w500/8ekxsUORMAsfmSc8GzHmG8gWPbp.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(321, 'api', 1562, '28 semanas después', 'Seis meses después de que la propagación del virus haya aniquilado las Islas Británicas, el ejército de los Estados Unidos declara que ha ganado la guerra contra la infección y que puede comenzar la reconstrucción del país. Con la primera ola de refugiados que vuelve al país, una familia consigue reencontrarse. Pero uno de los miembros guarda un terrible secreto sin ser consciente de ello. El virus aún no ha sido destruido y en esta ocasión, es más peligroso que nunca.', 'Terror, Suspense, Ciencia ficción', 2007, NULL, 'https://image.tmdb.org/t/p/w500/8PJEQ63WebiLJ4vtuyXx68Jehmh.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(322, 'api', 1241982, 'Vaiana 2', 'Tras recibir una inesperada llamada de sus antepasados, Vaiana debe viajar a los lejanos mares de Oceanía y adentrarse en peligrosas aguas perdidas para vivir una aventura sin precedentes.', 'Animación, Aventura, Familia, Comedia', 2024, NULL, 'https://image.tmdb.org/t/p/w500/b1WsCRfomw7tRi12NuseKsAJxYK.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(323, 'api', 1241436, 'Warfare: Tiempo de guerra', 'Basada en las experiencias reales del ex marine Ray Mendoza (codirector y coguionista de la película) durante la guerra de Irak. Introduce al espectador en la experiencia de un pelotón de Navy SEALs estadounidenses. Concretamente en una misión de vigilancia que se tuerce en territorio insurgente. Una historia visceral y a pie de campo sobre la guerra moderna y la hermandad, contada como nunca antes: en tiempo real y basada en los recuerdos de quienes la vivieron.', 'Bélica, Acción', 2025, NULL, 'https://image.tmdb.org/t/p/w500/i0vm0Iot5gN8XfhzZIWeoOX7rcw.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(324, 'api', 7451, 'xXx', 'Xander Cage es XXX, un antiguo ganador de X-Games y atleta profesional de deportes de extremo, que sobrevive vendiendo videos de sus increíbles hazañas, las cuales hacen emitir adrenalina por todo el cuerpo. Pero después de incontables encuentros con la ley, su mundo está a punto de tomar un rumbo aún más extremo... Porque Xander no sabe que ha sido \"espiado\" por Augustus Gibbons, un agente veterano de la Agencia Nacional de Seguridad que se encuentra en una desesperada situación en la distante ciudad de Praga, en donde su operativo secreto ha sido asesinado por una pandilla de mafiosos con un estilo muy propio, que se llaman así mismos Anarchy 99, encabezados por el brutal ex-Comandante del Ejército Ruso Yorgi.', 'Acción, Aventura, Suspense, Crimen', 2002, NULL, 'https://image.tmdb.org/t/p/w500/gd4hRY3pFXRY7YVbMdVBpnKV7wC.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(325, 'api', 1449951, 'Snow White', 'Cuando los celos de la Reina se vuelven mortales, Blancanieves debe unir fuerzas con los gnomos para luchar contra la ira de su madrastra.', 'Animación, Familia, Fantasía', 2025, NULL, 'https://image.tmdb.org/t/p/w500/pEK0Ir45l1e0QvpBTsCu0hVwTDm.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(326, 'api', 757725, 'Shadow Force', 'Con una recompensa por sus cabezas, la pareja formada por Omar Sy y Kerry Washington, huye con su hijo para escapar de su antigua organización: una unidad de operaciones en la sombra que ha sido enviada para liquidarlos.', 'Acción, Drama, Suspense', 2025, NULL, 'https://image.tmdb.org/t/p/w500/7IEW24vBiZerZrDlgLVSUU3oT1C.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(327, 'api', 822119, 'Capitán América: Brave New World', 'Tras reunirse con el recién elegido presidente de los EE. UU., Thaddeus Ross, Sam se encuentra en medio de un incidente internacional. Debe descubrir el motivo que se esconde tras un perverso complot global, antes de que su verdadero artífice enfurezca al mundo entero.', 'Acción, Suspense, Ciencia ficción', 2025, NULL, 'https://image.tmdb.org/t/p/w500/vUNj55xlF0pSU5FU3yDHC6L5wVX.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(328, 'api', 82702, 'Cómo entrenar a tu dragón 2', 'Han pasado cinco años desde que Hipo empezó a entrenar a su dragón, rompiendo la tradición vikinga de cazarlos. Astrid y el resto de la pandilla han conseguido difundir en la isla un nuevo deporte: las carreras de dragones. Mientras realizan una carrera, atraviesan los cielos llegando a territorios inhóspitos, donde nadie antes ha estado. Durante un viaje descubren una cueva cubierta de hielo que resulta ser el refugio de cientos de dragones salvajes, a los que cuida un misterioso guardián. Hipo y los suyos se unen al guardián para proteger a los dragones de las fuerzas malignas que quieren acabar con ellos.', 'Fantasía, Acción, Aventura, Animación, Comedia, Familia', 2014, NULL, 'https://image.tmdb.org/t/p/w500/bXcRw7FL8QzUZOvY2fZdwDv5ymQ.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(329, 'api', 939243, 'Sonic 3: La película', 'Sonic, Knuckles y Tails se reúnen para enfrentarse a un nuevo y poderoso adversario, Shadow, un misterioso villano cuyos poderes no se parecen a nada de lo que nuestros héroes han conocido hasta ahora. Con sus facultades superadas en todos los sentidos, el Equipo Sonic tendrá que establecer una insólita alianza con la esperanza de detener a Shadow y proteger el planeta.', 'Acción, Ciencia ficción, Comedia, Familia', 2024, NULL, 'https://image.tmdb.org/t/p/w500/3aDWCRXLYOCuxjrjiPfLd79tcI6.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(330, 'api', 1010581, 'Culpa mía', 'Noah debe dejar su ciudad, novio y amigos para mudarse a la mansión de William Leister, el flamante y rico marido de su madre Rafaela. Por otro lado, con 17 años, orgullosa e independiente, Noah se resiste a vivir en una mansión rodeada de lujo. Allí conoce a Nick, su nuevo hermanastro, y el choque de sus fuertes personalidades se hace evidente desde el primer momento.', 'Romance, Drama', 2023, NULL, 'https://image.tmdb.org/t/p/w500/gp31EwMH5D2bftOjscwkgTmoLAB.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(331, 'api', 1232546, 'Until Dawn', 'Un año después de la misteriosa desaparición de su hermana Melanie, Clover y sus amigas se dirigen al remoto valle donde desapareció en busca de respuestas. Mientras exploran un centro de visitantes abandonado, son acechadas por un asesino enmascarado que las mata una a una de forma horrible… para después despertar y encontrarse de nuevo al principio de la misma noche.', 'Terror, Misterio', 2025, NULL, 'https://image.tmdb.org/t/p/w500/exgfubqSbF4veI4uXFOdbV66gEf.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(332, 'api', 1011477, 'Karate Kid: Legends', 'Tras una tragedia familiar, el prodigio del kung fu Li Fong se ve obligado a abandonar su hogar en Pekín y trasladarse a Nueva York con su madre. Cuando un nuevo amigo necesita su ayuda, Li se presenta a una competición de kárate, pero sus habilidades no son suficientes. El profesor de kung fu de Li, el Sr. Han, pide ayuda al Karate Kid original, Daniel LaRusso, y Li aprende una nueva forma de luchar, fusionando sus dos estilos en uno solo para el enfrentamiento definitivo de artes marciales.', 'Acción, Aventura, Drama', 2025, NULL, 'https://image.tmdb.org/t/p/w500/efNhiZPk71FTYJ30dBkWMfc939D.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(333, 'api', 166428, 'Cómo entrenar a tu dragón 3', 'Lo que comenzó como la inesperada amistad entre un joven vikingo y un temible dragón, Furia Nocturna,  se ha convertido en una épica trilogía que ha recorrido sus vidas. En esta nueva entrega, Hipo y Desdentao descubrirán finalmente su verdadero destino: para uno, gobernar Isla Mema junto a Astrid; para el otro, ser el líder de su especie. Pero, por el camino, deberán poner a prueba los lazos que los unen, plantando cara a la mayor amenaza que jamás haya afrontado... y a la aparición de una Furia Nocturna hembra.', 'Animación, Familia, Aventura', 2019, NULL, 'https://image.tmdb.org/t/p/w500/rhnoAReqO3Xoe6GbJhT3409lnq8.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(334, 'api', 1001435, 'La vida ante nosotros', 'París. Julio 1942. A medida que se multiplican las redadas a familias judías, Tauba, de 13 años, y sus padres encuentran refugio en una habitación minúscula, bajo los tejados de la ciudad. A pesar de las circunstancias extremas de su vida, limitada a una buhardilla de sólo 6 metros cuadrados, Tauba se aferrará a la esperanza, encontrando alegría en las pequeñas cosas durante la ocupación nazi.', 'Drama, Historia, Bélica', 2025, NULL, 'https://image.tmdb.org/t/p/w500/6coVK78FwwHFxvdguWDFlQp1pKi.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:52', '2025-06-26 21:26:13'),
(335, 'api', 324544, 'Tierras perdidas', 'Basada en el relato de George R. R. Martin. Una reina (Amara Okereke), desesperada por encontrar la felicidad en el amor, envía a la poderosa bruja Gray Alys (Milla Jovovich) a las Tierras Perdidas, en busca de un poder mágico que permite a una persona transformarse en un hombre lobo. Con el misterioso cazador Boyce (Dave Bautista), que la apoya en la lucha contra criaturas oscuras y despiadadas, Gray deambula por un mundo inquietante y peligroso. Pero solo ella sabe que, cada deseo que se concede, tiene consecuencias inimaginables.', 'Acción, Fantasía, Aventura', 2025, NULL, 'https://image.tmdb.org/t/p/w500/sLDxndoqFWwJEq7iEdYQBzPjUDQ.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:53', '2025-06-26 21:26:14'),
(336, 'api', 1124620, 'The Monkey', 'Cuando dos hermanos gemelos encuentran un misterioso mono de cuerda, una serie de muertes atroces separan a su familia. Veinticinco años después, el mono comienza una nueva matanza que obliga a los hermanos a enfrentarse al juguete maldito.', 'Terror, Comedia', 2025, NULL, 'https://image.tmdb.org/t/p/w500/aPAzmD8YAErjz5GzQ1tgkTlv28S.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:53', '2025-06-26 21:26:14'),
(337, 'api', 762509, 'Mufasa: El rey león', 'Rafiki debe transmitir la leyenda de Mufasa a la joven cachorro de león Kiara, hija de Simba y Nala, y con Timón y Pumba prestando su estilo característico. Mufasa, un cachorro huérfano, perdido y solo, conoce a un simpático león llamado Taka, heredero de un linaje real. Este encuentro casual pone en marcha un viaje de un extraordinario grupo de inadaptados que buscan su destino.', 'Aventura, Familia, Animación', 2024, NULL, 'https://image.tmdb.org/t/p/w500/dmw74cWIEKaEgl5Dv3kUTcCob6D.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:53', '2025-06-26 21:26:14'),
(338, 'api', 1071585, 'M3GAN 2.0', 'Con el futuro de la existencia humana en juego, Gemma se da cuenta de que la única opción es resucitar a M3GAN y darle unas cuantas mejoras, haciéndola más rápida, más fuerte y más letal.', 'Acción, Terror, Ciencia ficción', 2025, NULL, 'https://image.tmdb.org/t/p/w500/w8llBJfNIJoQY9UV8p1eOBTyEjP.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:53', '2025-06-26 21:26:14'),
(339, 'api', 1232933, 'Terror en el Río', 'Un equipo de buceadores se enfrenta a un tiburón toro en las profundidades y a un grupo de despiadados criminales en la superficie.', 'Terror, Acción, Aventura, Suspense', 2025, NULL, 'https://image.tmdb.org/t/p/w500/kIO7eVOivYH9LptLxdXio5KRor.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:53', '2025-06-26 21:26:14'),
(340, 'api', 950396, 'El abismo secreto', 'Mandan a dos operativos de élite a vigilar lados opuestos de un misterioso abismo y allí intiman desde la distancia, pero deberán aunar fuerzas para sobrevivir al mal que esconde el abismo.', 'Romance, Ciencia ficción, Suspense', 2025, NULL, 'https://image.tmdb.org/t/p/w500/3s0jkMh0YUhIeIeioH3kt2X4st4.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:53', '2025-06-26 21:26:14'),
(341, 'api', 912649, 'Venom: El último baile', 'Eddie y Venom están a la fuga. Perseguidos por sus sendos mundos y cada vez más cercados, el dúo se ve abocado a tomar una decisión devastadora que hará que caiga el telón sobre el último baile de Venom y Eddie.', 'Acción, Ciencia ficción, Aventura', 2024, NULL, 'https://image.tmdb.org/t/p/w500/8F74DwgFxTIBNtbqSLjR7zWmnHh.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:53', '2025-06-26 21:26:14'),
(342, 'api', 11544, 'Lilo & Stitch', 'Lilo es una niña hawaiana que se siente sola y decide adoptar un \"perro\" muy feo al que llama Stitch. Stitch sería la mascota perfecta... si no fuera en realidad un experimento genético que ha escapado de un planeta alienígena y que ha aterrizado por casualidad en la tierra.', 'Animación, Familia, Comedia', 2002, NULL, 'https://image.tmdb.org/t/p/w500/9jrmKyhNGam2pj89bcxmhQzXCNo.jpg', '', NULL, 'G', 'activa', '2025-06-26 21:25:53', '2025-06-26 21:26:14'),
(343, 'manual', NULL, 'El chino y el Negro', 'gsf', 'Acción, Aventura, Crimen', 2025, 130, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQg0RgxMp25fQYJ7yh9m7tw_Gz-eC4HgMgcwg&s', 'https://www.youtube.com/watch?v=DYV38YKPTJk&list=RDDYV38YKPTJk&start_radio=1', 'gdgffdfd', NULL, 'activa', '2025-06-26 21:33:30', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `salas`
--

CREATE TABLE `salas` (
  `id_sala` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `capacidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `salas`
--

INSERT INTO `salas` (`id_sala`, `nombre`, `capacidad`) VALUES
(1, 'Sala 1', 120),
(2, 'Sala 2', 120),
(3, 'Sala 3', 120),
(4, 'Sala VIP', 120),
(5, 'Sala 4DX', 120),
(6, 'Sala Infantil', 120);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('usuario','admin') NOT NULL DEFAULT 'usuario',
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_usuario`, `email`, `password_hash`, `rol`, `fecha_registro`) VALUES
(2, 'juanperez', 'juan.perez@gmail.com', '$2y$10$abcdefg1234567', 'usuario', '2024-06-10 12:30:00'),
(3, 'maria88', 'maria88@hotmail.com', '$2y$10$hijklmn8901234', 'usuario', '2024-06-11 09:15:00'),
(4, 'cinelover', 'cinelover@outlook.com', '$2y$10$opqrstuv567890', 'usuario', '2024-06-12 18:45:00'),
(5, 'sofia', 'sofia@gmail.com', '$2y$10$wxyzabcd234567', 'usuario', '2024-06-13 14:20:00'),
(6, 'lucas', 'lucas@cine.com', '$2y$10$lucaspass', 'usuario', '2024-06-14 11:00:00'),
(7, 'valentina', 'valen@cine.com', '$2y$10$valenpass', 'usuario', '2024-06-15 16:30:00'),
(8, 'rodrigo', 'rodrigo@cine.com', '$2y$10$rodpass', 'usuario', '2024-06-16 17:45:00'),
(9, 'martina', 'martina@cine.com', '$2y$10$martipass', 'usuario', '2024-06-17 19:00:00'),
(10, 'tomas', 'tomas@cine.com', '$2y$10$tompass', 'usuario', '2024-06-18 20:10:00'),
(83, 'admin', 'tomasiezzi16@gmail.com', '$2y$10$GEhHxifuUSL9Hmuk1TAqJeu5n69oCOHBtwTFXH.MT9raTZw51QmZO', 'admin', '2025-06-26 21:43:39');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id_comentario`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_pelicula` (`id_pelicula`),
  ADD KEY `idx_fecha_comentario` (`fecha_comentario`),
  ADD KEY `idx_valoracion` (`valoracion`);

--
-- Indices de la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD PRIMARY KEY (`id_entrada`),
  ADD UNIQUE KEY `asiento_unico` (`id_pelicula`,`fila`,`numero_asiento`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `idx_fecha_compra` (`fecha_compra`),
  ADD KEY `fk_entradas_funcion` (`id_funcion`);

--
-- Indices de la tabla `funciones`
--
ALTER TABLE `funciones`
  ADD PRIMARY KEY (`id_funcion`),
  ADD KEY `idx_pelicula` (`id_pelicula`),
  ADD KEY `idx_sala` (`id_sala`),
  ADD KEY `idx_fecha_hora` (`fecha_hora`);

--
-- Indices de la tabla `peliculas`
--
ALTER TABLE `peliculas`
  ADD PRIMARY KEY (`id_pelicula`),
  ADD UNIQUE KEY `tmdb_id_unique` (`tmdb_id`),
  ADD KEY `idx_titulo` (`titulo`),
  ADD KEY `idx_genero` (`genero`),
  ADD KEY `idx_origen` (`origen`),
  ADD KEY `idx_trailer_url` (`trailer_url`(255)),
  ADD KEY `idx_actores` (`actores`(255));

--
-- Indices de la tabla `salas`
--
ALTER TABLE `salas`
  ADD PRIMARY KEY (`id_sala`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rol` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `entradas`
--
ALTER TABLE `entradas`
  MODIFY `id_entrada` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `funciones`
--
ALTER TABLE `funciones`
  MODIFY `id_funcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `peliculas`
--
ALTER TABLE `peliculas`
  MODIFY `id_pelicula` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=344;

--
-- AUTO_INCREMENT de la tabla `salas`
--
ALTER TABLE `salas`
  MODIFY `id_sala` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`id_pelicula`) REFERENCES `peliculas` (`id_pelicula`) ON DELETE CASCADE;

--
-- Filtros para la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD CONSTRAINT `entradas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `entradas_ibfk_2` FOREIGN KEY (`id_pelicula`) REFERENCES `peliculas` (`id_pelicula`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_entradas_funcion` FOREIGN KEY (`id_funcion`) REFERENCES `funciones` (`id_funcion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `funciones`
--
ALTER TABLE `funciones`
  ADD CONSTRAINT `funciones_ibfk_1` FOREIGN KEY (`id_pelicula`) REFERENCES `peliculas` (`id_pelicula`) ON DELETE CASCADE,
  ADD CONSTRAINT `funciones_ibfk_2` FOREIGN KEY (`id_sala`) REFERENCES `salas` (`id_sala`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
