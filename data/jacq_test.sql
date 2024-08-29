--
-- PostgreSQL database dump
--

-- Dumped from database version 16.2 (Debian 16.2-1.pgdg110+2)
-- Dumped by pg_dump version 16.3

-- Started on 2024-08-29 06:38:54 UTC

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 215 (class 1259 OID 16507)
-- Name: doctrine_migrations; Type: TABLE; Schema: public; Owner: jacq_test
--

CREATE TABLE public.doctrine_migrations (
    version character varying(191) NOT NULL,
    executed_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    execution_time integer
);


ALTER TABLE public.doctrine_migrations OWNER TO jacq_test;

--
-- TOC entry 217 (class 1259 OID 16514)
-- Name: herbaria; Type: TABLE; Schema: public; Owner: jacq_test
--

CREATE TABLE public.herbaria (
    id integer NOT NULL,
    acronym character varying(255) NOT NULL
);


ALTER TABLE public.herbaria OWNER TO jacq_test;

--
-- TOC entry 3359 (class 0 OID 0)
-- Dependencies: 217
-- Name: TABLE herbaria; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON TABLE public.herbaria IS 'List of involved herbaria';


--
-- TOC entry 3360 (class 0 OID 0)
-- Dependencies: 217
-- Name: COLUMN herbaria.acronym; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON COLUMN public.herbaria.acronym IS 'Acronym of herbarium according to Index Herbariorum';


--
-- TOC entry 216 (class 1259 OID 16513)
-- Name: herbaria_id_seq; Type: SEQUENCE; Schema: public; Owner: jacq_test
--

CREATE SEQUENCE public.herbaria_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.herbaria_id_seq OWNER TO jacq_test;

--
-- TOC entry 3362 (class 0 OID 0)
-- Dependencies: 216
-- Name: herbaria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: jacq_test
--

ALTER SEQUENCE public.herbaria_id_seq OWNED BY public.herbaria.id;


--
-- TOC entry 219 (class 1259 OID 16522)
-- Name: photos; Type: TABLE; Schema: public; Owner: jacq_test
--

CREATE TABLE public.photos (
    id integer NOT NULL,
    herbarium_id integer,
    archive_filename character varying(255) NOT NULL,
    jp2filename character varying(255) NOT NULL,
    specimen_id character varying(255) DEFAULT NULL::character varying,
    width integer,
    height integer,
    archive_file_size bigint,
    jp2file_size bigint,
    finalized boolean NOT NULL,
    message text,
    created_at timestamp(0) without time zone NOT NULL
);


ALTER TABLE public.photos OWNER TO jacq_test;

--
-- TOC entry 3364 (class 0 OID 0)
-- Dependencies: 219
-- Name: TABLE photos; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON TABLE public.photos IS 'Specimen photos';


--
-- TOC entry 3365 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN photos.herbarium_id; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON COLUMN public.photos.herbarium_id IS 'Herbarium storing and managing the specimen data';


--
-- TOC entry 3366 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN photos.archive_filename; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON COLUMN public.photos.archive_filename IS 'Filename of Archive Master TIF file';


--
-- TOC entry 3367 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN photos.jp2filename; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON COLUMN public.photos.jp2filename IS 'Filename of JP2 file';


--
-- TOC entry 3368 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN photos.specimen_id; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON COLUMN public.photos.specimen_id IS 'Herbarium internal unique id of specimen in form without herbarium acronym';


--
-- TOC entry 3369 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN photos.width; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON COLUMN public.photos.width IS 'Width of image with pixels';


--
-- TOC entry 3370 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN photos.height; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON COLUMN public.photos.height IS 'Height of image in pixels';


--
-- TOC entry 3371 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN photos.archive_file_size; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON COLUMN public.photos.archive_file_size IS 'Filesize of Archive Master TIFF file in bytes';


--
-- TOC entry 3372 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN photos.jp2file_size; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON COLUMN public.photos.jp2file_size IS 'Filesize of converted JP2 file in bytes';


--
-- TOC entry 3373 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN photos.finalized; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON COLUMN public.photos.finalized IS 'Flag with not finally usage decided yet';


--
-- TOC entry 3374 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN photos.message; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON COLUMN public.photos.message IS 'Result of migration';


--
-- TOC entry 3375 (class 0 OID 0)
-- Dependencies: 219
-- Name: COLUMN photos.created_at; Type: COMMENT; Schema: public; Owner: jacq_test
--

COMMENT ON COLUMN public.photos.created_at IS '(DC2Type:datetime_immutable)';


--
-- TOC entry 218 (class 1259 OID 16521)
-- Name: photos_id_seq; Type: SEQUENCE; Schema: public; Owner: jacq_test
--

CREATE SEQUENCE public.photos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.photos_id_seq OWNER TO jacq_test;

--
-- TOC entry 3377 (class 0 OID 0)
-- Dependencies: 218
-- Name: photos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: jacq_test
--

ALTER SEQUENCE public.photos_id_seq OWNED BY public.photos.id;


--
-- TOC entry 3190 (class 2604 OID 16517)
-- Name: herbaria id; Type: DEFAULT; Schema: public; Owner: jacq_test
--

ALTER TABLE ONLY public.herbaria ALTER COLUMN id SET DEFAULT nextval('public.herbaria_id_seq'::regclass);


--
-- TOC entry 3191 (class 2604 OID 16525)
-- Name: photos id; Type: DEFAULT; Schema: public; Owner: jacq_test
--

ALTER TABLE ONLY public.photos ALTER COLUMN id SET DEFAULT nextval('public.photos_id_seq'::regclass);


--
-- TOC entry 3347 (class 0 OID 16507)
-- Dependencies: 215
-- Data for Name: doctrine_migrations; Type: TABLE DATA; Schema: public; Owner: jacq_test
--

COPY public.doctrine_migrations (version, executed_at, execution_time) FROM stdin;
Database\\Migrations\\Version20240718123809	2024-07-29 16:23:30	9
\.


--
-- TOC entry 3349 (class 0 OID 16514)
-- Dependencies: 217
-- Data for Name: herbaria; Type: TABLE DATA; Schema: public; Owner: jacq_test
--

COPY public.herbaria (id, acronym) FROM stdin;
1	PRC
\.


--
-- TOC entry 3351 (class 0 OID 16522)
-- Dependencies: 219
-- Data for Name: photos; Type: TABLE DATA; Schema: public; Owner: jacq_test
--

COPY public.photos (id, herbarium_id, archive_filename, jp2filename, specimen_id, width, height, archive_file_size, jp2file_size, finalized, message, created_at) FROM stdin;
14	1	prc_478542.tif	prc_478542.jp2	478542	9539	13405	383675272	\N	f	db update record error (Error executing "HeadObject" on "https://test-iiif.s3.cl4.du.cesnet.cz/prc_478542.jp2"; AWS HTTP error: Client error: `HEAD https://test-iiif.s3.cl4.du.cesnet.cz/prc_478542.jp2` resulted in a `404 Not Found` response NotFound (client): 404 Not Found (Request-ID: tx00000d57dfbb1ae3a42ee-0066b22e24-980ec3d-storage-cl4) - )	2024-07-31 15:00:11
15	1	prc_478543.tif	prc_478543.jp2	478543	9539	13405	383675272	\N	f	db update record error (Error executing "HeadObject" on "https://test-iiif.s3.cl4.du.cesnet.cz/prc_478543.jp2"; AWS HTTP error: Client error: `HEAD https://test-iiif.s3.cl4.du.cesnet.cz/prc_478543.jp2` resulted in a `404 Not Found` response NotFound (client): 404 Not Found (Request-ID: tx000007f4a9ad6f8ce91f5-0066b22e28-9870286-storage-cl4) - )	2024-07-31 15:00:11
1	\N	geranium krylovii.tif	geranium krylovii.jp2	\N	\N	\N	\N	\N	f	Problem to detect JP2 presence: Client error: `GET https://herbarium-iiif.dyn.cloud.e-infra.cz/iiif/3/geranium%20krylovii.jp2` resulted in a `404 Not Found` response:\n404 Not Found\n\niiif/geranium krylovii.jp2\n\n\njava.nio.file.NoSuchFileException: iiif/geranium krylovii.jp2\n\tat edu.illino (truncated...)\n	2024-07-31 15:00:11
2	1	prc_000037.tif	prc_000037.jp2	37	7305	8604	188583524	21614950	t	\N	2024-07-31 15:00:11
3	1	prc_000037_a.tif	prc_000037_a.jp2	37	7305	7504	164478068	20864102	t	\N	2024-07-31 15:00:11
4	1	prc_000037_b.tif	prc_000037_b.jp2	37	7305	7504	164478096	20528714	t	\N	2024-07-31 15:00:11
5	1	prc_000038.tif	prc_000038.jp2	38	7305	8175	179182548	29053210	t	\N	2024-07-31 15:00:11
6	1	prc_001372.tif	prc_001372.jp2	1372	3731	7446	83368468	10095677	t	\N	2024-07-31 15:00:11
7	1	prc_003971.tif	prc_003971.jp2	3971	4717	5145	72832768	7165357	t	\N	2024-07-31 15:00:11
8	1	prc_3809.tif	prc_3809.jp2	3809	4374	5946	78048892	\N	f	db update record error (Error executing "HeadObject" on "https://test-iiif.s3.cl4.du.cesnet.cz/prc_3809.jp2"; AWS HTTP error: Client error: `HEAD https://test-iiif.s3.cl4.du.cesnet.cz/prc_3809.jp2` resulted in a `404 Not Found` response NotFound (client): 404 Not Found (Request-ID: tx000003aa63255ffcd4e50-0066b22e0f-980ecc4-storage-cl4) - )	2024-07-31 15:00:11
9	1	prc_3810.tif	prc_3810.jp2	3810	4374	6103	80109116	\N	f	db update record error (Error executing "HeadObject" on "https://test-iiif.s3.cl4.du.cesnet.cz/prc_3810.jp2"; AWS HTTP error: Client error: `HEAD https://test-iiif.s3.cl4.du.cesnet.cz/prc_3810.jp2` resulted in a `404 Not Found` response NotFound (client): 404 Not Found (Request-ID: tx0000025d079c89c95778a-0066b22e13-98702c2-storage-cl4) - )	2024-07-31 15:00:11
10	1	prc_3812.tif	prc_3812.jp2	3812	5061	6689	101584280	\N	f	db update record error (Error executing "HeadObject" on "https://test-iiif.s3.cl4.du.cesnet.cz/prc_3812.jp2"; AWS HTTP error: Client error: `HEAD https://test-iiif.s3.cl4.du.cesnet.cz/prc_3812.jp2` resulted in a `404 Not Found` response NotFound (client): 404 Not Found (Request-ID: tx00000b39e13adcfc67fba-0066b22e17-a444350-storage-cl4) - )	2024-07-31 15:00:11
11	1	prc_478539.tif	prc_478539.jp2	478539	9459	13309	377733880	\N	f	db update record error (Error executing "HeadObject" on "https://test-iiif.s3.cl4.du.cesnet.cz/prc_478539.jp2"; AWS HTTP error: Client error: `HEAD https://test-iiif.s3.cl4.du.cesnet.cz/prc_478539.jp2` resulted in a `404 Not Found` response NotFound (client): 404 Not Found (Request-ID: tx000006666a6dea111c918-0066b22e1a-980ec3d-storage-cl4) - )	2024-07-31 15:00:11
12	1	prc_478540.tif	prc_478540.jp2	478540	9283	13325	\N	\N	f	No barcode was detected	2024-07-31 15:00:11
13	1	prc_478541.tif	prc_478541.jp2	478541	9283	13373	372487624	\N	f	db update record error (Error executing "HeadObject" on "https://test-iiif.s3.cl4.du.cesnet.cz/prc_478541.jp2"; AWS HTTP error: Client error: `HEAD https://test-iiif.s3.cl4.du.cesnet.cz/prc_478541.jp2` resulted in a `404 Not Found` response NotFound (client): 404 Not Found (Request-ID: tx000000562f0332f83ceea-0066b22e21-980ec3d-storage-cl4) - )	2024-07-31 15:00:11
\.


--
-- TOC entry 3379 (class 0 OID 0)
-- Dependencies: 216
-- Name: herbaria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: jacq_test
--

SELECT pg_catalog.setval('public.herbaria_id_seq', 1, true);


--
-- TOC entry 3380 (class 0 OID 0)
-- Dependencies: 218
-- Name: photos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: jacq_test
--

SELECT pg_catalog.setval('public.photos_id_seq', 15, true);


--
-- TOC entry 3194 (class 2606 OID 16512)
-- Name: doctrine_migrations doctrine_migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: jacq_test
--

ALTER TABLE ONLY public.doctrine_migrations
    ADD CONSTRAINT doctrine_migrations_pkey PRIMARY KEY (version);


--
-- TOC entry 3196 (class 2606 OID 16519)
-- Name: herbaria herbaria_pkey; Type: CONSTRAINT; Schema: public; Owner: jacq_test
--

ALTER TABLE ONLY public.herbaria
    ADD CONSTRAINT herbaria_pkey PRIMARY KEY (id);


--
-- TOC entry 3200 (class 2606 OID 16530)
-- Name: photos photos_pkey; Type: CONSTRAINT; Schema: public; Owner: jacq_test
--

ALTER TABLE ONLY public.photos
    ADD CONSTRAINT photos_pkey PRIMARY KEY (id);


--
-- TOC entry 3198 (class 1259 OID 16533)
-- Name: idx_876e0d9dd127992; Type: INDEX; Schema: public; Owner: jacq_test
--

CREATE INDEX idx_876e0d9dd127992 ON public.photos USING btree (herbarium_id);


--
-- TOC entry 3197 (class 1259 OID 16520)
-- Name: uniq_40df22ba512d8851; Type: INDEX; Schema: public; Owner: jacq_test
--

CREATE UNIQUE INDEX uniq_40df22ba512d8851 ON public.herbaria USING btree (acronym);


--
-- TOC entry 3201 (class 1259 OID 16531)
-- Name: uniq_876e0d911642609; Type: INDEX; Schema: public; Owner: jacq_test
--

CREATE UNIQUE INDEX uniq_876e0d911642609 ON public.photos USING btree (archive_filename);


--
-- TOC entry 3202 (class 1259 OID 16532)
-- Name: uniq_876e0d9765b2490; Type: INDEX; Schema: public; Owner: jacq_test
--

CREATE UNIQUE INDEX uniq_876e0d9765b2490 ON public.photos USING btree (jp2filename);


--
-- TOC entry 3203 (class 2606 OID 16534)
-- Name: photos fk_876e0d9dd127992; Type: FK CONSTRAINT; Schema: public; Owner: jacq_test
--

ALTER TABLE ONLY public.photos
    ADD CONSTRAINT fk_876e0d9dd127992 FOREIGN KEY (herbarium_id) REFERENCES public.herbaria(id);


--
-- TOC entry 3357 (class 0 OID 0)
-- Dependencies: 5
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pg_database_owner
--

GRANT ALL ON SCHEMA public TO jacq_test;


--
-- TOC entry 3358 (class 0 OID 0)
-- Dependencies: 215
-- Name: TABLE doctrine_migrations; Type: ACL; Schema: public; Owner: jacq_test
--

GRANT ALL ON TABLE public.doctrine_migrations TO jacq;


--
-- TOC entry 3361 (class 0 OID 0)
-- Dependencies: 217
-- Name: TABLE herbaria; Type: ACL; Schema: public; Owner: jacq_test
--

GRANT ALL ON TABLE public.herbaria TO jacq;


--
-- TOC entry 3363 (class 0 OID 0)
-- Dependencies: 216
-- Name: SEQUENCE herbaria_id_seq; Type: ACL; Schema: public; Owner: jacq_test
--

GRANT ALL ON SEQUENCE public.herbaria_id_seq TO jacq;


--
-- TOC entry 3376 (class 0 OID 0)
-- Dependencies: 219
-- Name: TABLE photos; Type: ACL; Schema: public; Owner: jacq_test
--

GRANT ALL ON TABLE public.photos TO jacq;


--
-- TOC entry 3378 (class 0 OID 0)
-- Dependencies: 218
-- Name: SEQUENCE photos_id_seq; Type: ACL; Schema: public; Owner: jacq_test
--

GRANT ALL ON SEQUENCE public.photos_id_seq TO jacq;


-- Completed on 2024-08-29 06:38:54 UTC

--
-- PostgreSQL database dump complete
--

