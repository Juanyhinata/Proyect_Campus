--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.25
-- Dumped by pg_dump version 9.5.25

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: campus; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA campus;


ALTER SCHEMA campus OWNER TO postgres;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: curso_usuario; Type: TABLE; Schema: campus; Owner: postgres
--

CREATE TABLE campus.curso_usuario (
    curso_id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    asignado_en timestamp with time zone DEFAULT now()
);


ALTER TABLE campus.curso_usuario OWNER TO postgres;

--
-- Name: cursos; Type: TABLE; Schema: campus; Owner: postgres
--

CREATE TABLE campus.cursos (
    id bigint NOT NULL,
    titulo character varying(200) NOT NULL,
    descripcion text,
    imagen character varying(255),
    activo boolean DEFAULT true,
    creado_en timestamp with time zone DEFAULT now()
);


ALTER TABLE campus.cursos OWNER TO postgres;

--
-- Name: cursos_id_seq; Type: SEQUENCE; Schema: campus; Owner: postgres
--

CREATE SEQUENCE campus.cursos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE campus.cursos_id_seq OWNER TO postgres;

--
-- Name: cursos_id_seq; Type: SEQUENCE OWNED BY; Schema: campus; Owner: postgres
--

ALTER SEQUENCE campus.cursos_id_seq OWNED BY campus.cursos.id;


--
-- Name: modulo_progreso; Type: TABLE; Schema: campus; Owner: postgres
--

CREATE TABLE campus.modulo_progreso (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    modulo_id bigint NOT NULL,
    porcentaje smallint DEFAULT 0,
    completado boolean DEFAULT false,
    ultima_actualizacion timestamp with time zone DEFAULT now(),
    tiempo_visto integer DEFAULT 0,
    ultimo_tiempo integer DEFAULT 0,
    CONSTRAINT modulo_progreso_porcentaje_check CHECK (((porcentaje >= 0) AND (porcentaje <= 100)))
);


ALTER TABLE campus.modulo_progreso OWNER TO postgres;

--
-- Name: modulo_progreso_id_seq; Type: SEQUENCE; Schema: campus; Owner: postgres
--

CREATE SEQUENCE campus.modulo_progreso_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE campus.modulo_progreso_id_seq OWNER TO postgres;

--
-- Name: modulo_progreso_id_seq; Type: SEQUENCE OWNED BY; Schema: campus; Owner: postgres
--

ALTER SEQUENCE campus.modulo_progreso_id_seq OWNED BY campus.modulo_progreso.id;


--
-- Name: modulos; Type: TABLE; Schema: campus; Owner: postgres
--

CREATE TABLE campus.modulos (
    id bigint NOT NULL,
    curso_id bigint NOT NULL,
    titulo character varying(200) NOT NULL,
    orden integer DEFAULT 0 NOT NULL,
    evaluacion_activa boolean DEFAULT false,
    creado_en timestamp with time zone DEFAULT now()
);


ALTER TABLE campus.modulos OWNER TO postgres;

--
-- Name: modulos_id_seq; Type: SEQUENCE; Schema: campus; Owner: postgres
--

CREATE SEQUENCE campus.modulos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE campus.modulos_id_seq OWNER TO postgres;

--
-- Name: modulos_id_seq; Type: SEQUENCE OWNED BY; Schema: campus; Owner: postgres
--

ALTER SEQUENCE campus.modulos_id_seq OWNED BY campus.modulos.id;


--
-- Name: temas; Type: TABLE; Schema: campus; Owner: postgres
--

CREATE TABLE campus.temas (
    id bigint NOT NULL,
    modulo_id bigint NOT NULL,
    titulo character varying(200) NOT NULL,
    tipo character varying(20) DEFAULT 'video'::character varying,
    video_id character varying(50),
    pdf_ruta character varying(255),
    orden integer DEFAULT 0 NOT NULL,
    duracion_segundos integer,
    CONSTRAINT temas_tipo_check CHECK (((tipo)::text = ANY ((ARRAY['video'::character varying, 'pdf'::character varying, 'texto'::character varying])::text[])))
);


ALTER TABLE campus.temas OWNER TO postgres;

--
-- Name: temas_id_seq; Type: SEQUENCE; Schema: campus; Owner: postgres
--

CREATE SEQUENCE campus.temas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE campus.temas_id_seq OWNER TO postgres;

--
-- Name: temas_id_seq; Type: SEQUENCE OWNED BY; Schema: campus; Owner: postgres
--

ALTER SEQUENCE campus.temas_id_seq OWNED BY campus.temas.id;


--
-- Name: usuarios; Type: TABLE; Schema: campus; Owner: postgres
--

CREATE TABLE campus.usuarios (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    email character varying(150) NOT NULL,
    password text NOT NULL,
    rol character varying(20) DEFAULT 'cliente'::character varying,
    creado_en timestamp with time zone DEFAULT now(),
    empresa character varying(150) DEFAULT 'Sin empresa'::character varying,
    activo boolean DEFAULT true,
    CONSTRAINT usuarios_rol_check CHECK (((rol)::text = ANY ((ARRAY['admin'::character varying, 'agente'::character varying, 'cliente'::character varying])::text[])))
);

-- 1. Tabla Evaluaciones
CREATE TABLE IF NOT EXISTS campus.evaluaciones (
    id SERIAL PRIMARY KEY,
    modulo_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (modulo_id) REFERENCES campus.modulos(id) ON DELETE CASCADE
);

-- 2. Tabla Preguntas
CREATE TABLE IF NOT EXISTS campus.preguntas (
    id SERIAL PRIMARY KEY,
    evaluacion_id INT NOT NULL,
    texto_pregunta TEXT NOT NULL,
    orden INT DEFAULT 0,
    FOREIGN KEY (evaluacion_id) REFERENCES campus.evaluaciones(id) ON DELETE CASCADE
);

-- 3. Tabla Opciones
CREATE TABLE IF NOT EXISTS campus.opciones (
    id SERIAL PRIMARY KEY,
    pregunta_id INT NOT NULL,
    texto_opcion TEXT NOT NULL,
    es_correcta BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (pregunta_id) REFERENCES campus.preguntas(id) ON DELETE CASCADE
);

-- 4. Tabla Intentos / Resultados
CREATE TABLE IF NOT EXISTS campus.intentos_evaluacion (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    evaluacion_id INT NOT NULL,
    calificacion DECIMAL(5,2) NOT NULL, -- 0 a 100
    aprobado BOOLEAN DEFAULT FALSE,
    fecha_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES campus.usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluacion_id) REFERENCES campus.evaluaciones(id) ON DELETE CASCADE
);


ALTER TABLE campus.usuarios OWNER TO postgres;

--
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: campus; Owner: postgres
--

CREATE SEQUENCE campus.usuarios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE campus.usuarios_id_seq OWNER TO postgres;

--
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: campus; Owner: postgres
--

ALTER SEQUENCE campus.usuarios_id_seq OWNED BY campus.usuarios.id;


--
-- Name: id; Type: DEFAULT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.cursos ALTER COLUMN id SET DEFAULT nextval('campus.cursos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.modulo_progreso ALTER COLUMN id SET DEFAULT nextval('campus.modulo_progreso_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.modulos ALTER COLUMN id SET DEFAULT nextval('campus.modulos_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.temas ALTER COLUMN id SET DEFAULT nextval('campus.temas_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.usuarios ALTER COLUMN id SET DEFAULT nextval('campus.usuarios_id_seq'::regclass);


--
-- Data for Name: curso_usuario; Type: TABLE DATA; Schema: campus; Owner: postgres
--

COPY campus.curso_usuario (curso_id, usuario_id, asignado_en) FROM stdin;
1	1	2025-11-27 19:38:21.499328-06
1	6	2025-11-30 01:14:50.12503-06
1	5	2025-12-01 17:23:04.457677-06
\.


--
-- Data for Name: cursos; Type: TABLE DATA; Schema: campus; Owner: postgres
--

COPY campus.cursos (id, titulo, descripcion, imagen, activo, creado_en) FROM stdin;
1	Arcadia CBOS	Curso de gestión con Arcadia	cbos.png	t	2025-11-28 01:58:05.166454-06
5	Local-BOS	Controles volumetricos	curso_1764632520.png	t	2025-12-01 17:42:00.818745-06
\.


--
-- Name: cursos_id_seq; Type: SEQUENCE SET; Schema: campus; Owner: postgres
--

SELECT pg_catalog.setval('campus.cursos_id_seq', 5, true);


--
-- Data for Name: modulo_progreso; Type: TABLE DATA; Schema: campus; Owner: postgres
--

COPY campus.modulo_progreso (id, usuario_id, modulo_id, porcentaje, completado, ultima_actualizacion, tiempo_visto, ultimo_tiempo) FROM stdin;
\.


--
-- Name: modulo_progreso_id_seq; Type: SEQUENCE SET; Schema: campus; Owner: postgres
--

SELECT pg_catalog.setval('campus.modulo_progreso_id_seq', 2, true);


--
-- Data for Name: modulos; Type: TABLE DATA; Schema: campus; Owner: postgres
--

COPY campus.modulos (id, curso_id, titulo, orden, evaluacion_activa, creado_en) FROM stdin;
2	1	Módulo 2: Inicio en CBOS	2	f	2025-11-27 19:38:21.499328-06
3	1	Módulo 3: Sección Mis Liquidaciones	3	f	2025-11-27 19:38:21.499328-06
4	1	Módulo 4: Mi Carburante	4	f	2025-11-27 19:38:21.499328-06
5	1	Módulo 5: Mi tienda	5	f	2025-11-27 19:38:21.499328-06
6	1	Módulo 6: Contabilización	6	f	2025-11-27 19:38:21.499328-06
7	1	Módulo 7: Consultas	7	f	2025-11-27 19:38:21.499328-06
8	1	Módulo 8: Mantenimiento	8	f	2025-11-27 19:38:21.499328-06
9	1	Módulo 9: Otros procesos	9	f	2025-11-27 19:38:21.499328-06
1	1	Módulo 1: Gestión de la tienda (CBOS)	1	t	2025-11-27 19:38:21.499328-06
14	5	Modulo 1. Introduccion a LocalBos	0	t	2025-12-01 17:44:02.121853-06
\.


--
-- Name: modulos_id_seq; Type: SEQUENCE SET; Schema: campus; Owner: postgres
--

SELECT pg_catalog.setval('campus.modulos_id_seq', 14, true);


--
-- Data for Name: temas; Type: TABLE DATA; Schema: campus; Owner: postgres
--

COPY campus.temas (id, modulo_id, titulo, tipo, video_id, pdf_ruta, orden, duracion_segundos) FROM stdin;
7	1	Administracion	video	https://youtu.be/mhccaKsU8-k	\N	0	\N
8	1	Material Ayuda	pdf	\N	pdf_1764631320.pdf	1	\N
\.


--
-- Name: temas_id_seq; Type: SEQUENCE SET; Schema: campus; Owner: postgres
--

SELECT pg_catalog.setval('campus.temas_id_seq', 8, true);


--
-- Data for Name: usuarios; Type: TABLE DATA; Schema: campus; Owner: postgres
--

COPY campus.usuarios (id, nombre, email, password, rol, creado_en, empresa, activo) FROM stdin;
1	Usuario Prueba	test@avalon.com	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	cliente	2025-11-27 19:38:21.499328-06	Sin empresa	t
2	Administrador	admin@campus.com	$2y$10$RxJrT0n6zgmaD5tXBPht6eIeF8gy.meN9Yq/3skFd1WjG4uo/iOCW	admin	2025-11-27 21:02:15.515176-06	Sin empresa	t
5	Cliente	cliente@campus.com	$2y$10$uws7sYNMF8Jrbug1RjgQH.PeleoJb0YIsjih0k0OjUxytyCatE35u	cliente	2025-11-30 00:45:43.723028-06	Empresa X	t
6	Juan Perez	agente@campus.com	$2y$10$NNjwLzMX0PrxUkKZ0/FY2es7u8kR0WBCgH4Atuien8o/N1GOTrPw.	agente	2025-11-30 00:47:51.263626-06	Avalon CAC	t
\.


--
-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: campus; Owner: postgres
--

SELECT pg_catalog.setval('campus.usuarios_id_seq', 6, true);


--
-- Name: curso_usuario_pkey; Type: CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.curso_usuario
    ADD CONSTRAINT curso_usuario_pkey PRIMARY KEY (curso_id, usuario_id);


--
-- Name: cursos_pkey; Type: CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.cursos
    ADD CONSTRAINT cursos_pkey PRIMARY KEY (id);


--
-- Name: modulo_progreso_pkey; Type: CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.modulo_progreso
    ADD CONSTRAINT modulo_progreso_pkey PRIMARY KEY (id);


--
-- Name: modulo_progreso_usuario_id_modulo_id_key; Type: CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.modulo_progreso
    ADD CONSTRAINT modulo_progreso_usuario_id_modulo_id_key UNIQUE (usuario_id, modulo_id);


--
-- Name: modulos_pkey; Type: CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.modulos
    ADD CONSTRAINT modulos_pkey PRIMARY KEY (id);


--
-- Name: temas_pkey; Type: CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.temas
    ADD CONSTRAINT temas_pkey PRIMARY KEY (id);


--
-- Name: usuarios_email_key; Type: CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.usuarios
    ADD CONSTRAINT usuarios_email_key UNIQUE (email);


--
-- Name: usuarios_pkey; Type: CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- Name: idx_curso_usuario_usuario; Type: INDEX; Schema: campus; Owner: postgres
--

CREATE INDEX idx_curso_usuario_usuario ON campus.curso_usuario USING btree (usuario_id);


--
-- Name: idx_modulos_curso; Type: INDEX; Schema: campus; Owner: postgres
--

CREATE INDEX idx_modulos_curso ON campus.modulos USING btree (curso_id);


--
-- Name: idx_progreso_modulo; Type: INDEX; Schema: campus; Owner: postgres
--

CREATE INDEX idx_progreso_modulo ON campus.modulo_progreso USING btree (modulo_id);


--
-- Name: idx_progreso_usuario; Type: INDEX; Schema: campus; Owner: postgres
--

CREATE INDEX idx_progreso_usuario ON campus.modulo_progreso USING btree (usuario_id);


--
-- Name: curso_usuario_curso_id_fkey; Type: FK CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.curso_usuario
    ADD CONSTRAINT curso_usuario_curso_id_fkey FOREIGN KEY (curso_id) REFERENCES campus.cursos(id) ON DELETE CASCADE;


--
-- Name: curso_usuario_usuario_id_fkey; Type: FK CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.curso_usuario
    ADD CONSTRAINT curso_usuario_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES campus.usuarios(id) ON DELETE CASCADE;


--
-- Name: modulo_progreso_modulo_id_fkey; Type: FK CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.modulo_progreso
    ADD CONSTRAINT modulo_progreso_modulo_id_fkey FOREIGN KEY (modulo_id) REFERENCES campus.modulos(id) ON DELETE CASCADE;


--
-- Name: modulo_progreso_usuario_id_fkey; Type: FK CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.modulo_progreso
    ADD CONSTRAINT modulo_progreso_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES campus.usuarios(id) ON DELETE CASCADE;


--
-- Name: modulos_curso_id_fkey; Type: FK CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.modulos
    ADD CONSTRAINT modulos_curso_id_fkey FOREIGN KEY (curso_id) REFERENCES campus.cursos(id) ON DELETE CASCADE;


--
-- Name: temas_modulo_id_fkey; Type: FK CONSTRAINT; Schema: campus; Owner: postgres
--

ALTER TABLE ONLY campus.temas
    ADD CONSTRAINT temas_modulo_id_fkey FOREIGN KEY (modulo_id) REFERENCES campus.modulos(id) ON DELETE CASCADE;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

