--
-- PostgreSQL database dump
--

-- Dumped from database version 17.3
-- Dumped by pg_dump version 17.3

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;


--
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


--
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: auth_role; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.auth_role (
    id character varying(50) NOT NULL,
    name character varying(100),
    description text
);


ALTER TABLE public.auth_role OWNER TO postgres;

--
-- Name: auth_user; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.auth_user (
    id character varying(36) NOT NULL,
    name character varying(255) NOT NULL,
    username character varying(255) NOT NULL,
    email character varying(100) NOT NULL,
    password character varying(60) NOT NULL,
    role_id character varying(36) NOT NULL,
    url_photo character varying,
    active boolean DEFAULT true,
    last_login timestamp without time zone,
    created_by character varying(36),
    created_at timestamp without time zone,
    updated_by character varying(36),
    updated_at timestamp without time zone,
    deleted_at timestamp with time zone,
    is_deleted boolean DEFAULT false,
    status character varying(1)
);


ALTER TABLE public.auth_user OWNER TO postgres;

--
-- Name: c_business_setting; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_business_setting (
    id character varying(36) DEFAULT (public.uuid_generate_v4())::character varying NOT NULL,
    business_name character varying(150) NOT NULL,
    phone_number character varying(20),
    address text,
    google_place_id character varying(255),
    latitude numeric(10,7),
    longitude numeric(10,7),
    down_payment_amount numeric(12,2) DEFAULT 50000,
    payment_expiry_minutes integer DEFAULT 60,
    maximum_delivery_km numeric(8,2),
    is_active boolean DEFAULT true,
    created_by character varying(36),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by character varying(36),
    updated_at timestamp without time zone,
    is_deleted boolean DEFAULT false,
    CONSTRAINT business_dp_check CHECK ((down_payment_amount >= (0)::numeric)),
    CONSTRAINT business_expiry_check CHECK ((payment_expiry_minutes > 0))
);


ALTER TABLE public.c_business_setting OWNER TO postgres;

--
-- Name: c_customer; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_customer (
    id character varying(36) DEFAULT (public.uuid_generate_v4())::character varying NOT NULL,
    full_name character varying(150) NOT NULL,
    phone_number character varying(20) NOT NULL,
    created_by character varying(36),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by character varying(36),
    updated_at timestamp without time zone,
    is_deleted boolean DEFAULT false
);


ALTER TABLE public.c_customer OWNER TO postgres;

--
-- Name: c_delivery_rate; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_delivery_rate (
    id character varying(36) DEFAULT (public.uuid_generate_v4())::character varying NOT NULL,
    name character varying(100) NOT NULL,
    minimum_distance_km numeric(8,2) DEFAULT 0 NOT NULL,
    maximum_distance_km numeric(8,2) NOT NULL,
    delivery_fee numeric(12,2) DEFAULT 0 NOT NULL,
    is_active boolean DEFAULT true,
    created_by character varying(36),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by character varying(36),
    updated_at timestamp without time zone,
    is_deleted boolean DEFAULT false,
    driver_fee numeric(12,2) DEFAULT 0 NOT NULL,
    company_fuel_deduction numeric(12,2) DEFAULT 0 NOT NULL,
    CONSTRAINT delivery_distance_check CHECK ((maximum_distance_km > minimum_distance_km)),
    CONSTRAINT delivery_fee_check CHECK ((delivery_fee >= (0)::numeric))
);


ALTER TABLE public.c_delivery_rate OWNER TO postgres;

--
-- Name: c_faq; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_faq (
    id character varying(36) DEFAULT (public.uuid_generate_v4())::character varying NOT NULL,
    question character varying(255) NOT NULL,
    answer text NOT NULL,
    seq integer DEFAULT 0,
    is_published boolean DEFAULT true,
    created_by character varying(36),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by character varying(36),
    updated_at timestamp without time zone,
    is_deleted boolean DEFAULT false
);


ALTER TABLE public.c_faq OWNER TO postgres;

--
-- Name: c_menu; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_menu (
    id character varying(36) DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying,
    link character varying(100),
    icon character varying(50),
    description text,
    permission_label character varying(50),
    action character varying(50),
    level integer,
    seq integer,
    created_by character varying(36),
    created_at timestamp without time zone,
    updated_by character varying(36),
    updated_at timestamp without time zone,
    is_deleted boolean DEFAULT false,
    parent_id character varying(36)
);


ALTER TABLE public.c_menu OWNER TO postgres;

--
-- Name: c_playstation_unit; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_playstation_unit (
    id character varying(36) DEFAULT (public.uuid_generate_v4())::character varying NOT NULL,
    unit_code character varying(30) NOT NULL,
    name character varying(100) NOT NULL,
    console_type character varying(30),
    serial_number character varying(100),
    status character varying(30) DEFAULT 'available'::character varying,
    notes text,
    is_active boolean DEFAULT true,
    created_by character varying(36),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by character varying(36),
    updated_at timestamp without time zone,
    is_deleted boolean DEFAULT false
);


ALTER TABLE public.c_playstation_unit OWNER TO postgres;

--
-- Name: c_rental_package; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_rental_package (
    id character varying(36) DEFAULT (public.uuid_generate_v4())::character varying NOT NULL,
    code character varying(30) NOT NULL,
    name character varying(100) NOT NULL,
    duration_hours integer NOT NULL,
    price numeric(12,2) DEFAULT 0 NOT NULL,
    blocked_start_time time without time zone,
    blocked_end_time time without time zone,
    description text,
    is_active boolean DEFAULT true,
    created_by character varying(36),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by character varying(36),
    updated_at timestamp without time zone,
    is_deleted boolean DEFAULT false,
    CONSTRAINT rental_duration_check CHECK ((duration_hours > 0)),
    CONSTRAINT rental_price_check CHECK ((price >= (0)::numeric))
);


ALTER TABLE public.c_rental_package OWNER TO postgres;

--
-- Name: c_terms_condition; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.c_terms_condition (
    id character varying(36) DEFAULT (public.uuid_generate_v4())::character varying NOT NULL,
    title character varying(150) NOT NULL,
    content text NOT NULL,
    version integer DEFAULT 1 NOT NULL,
    is_active boolean DEFAULT true,
    created_by character varying(36),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by character varying(36),
    updated_at timestamp without time zone,
    is_deleted boolean DEFAULT false,
    CONSTRAINT terms_version_check CHECK ((version > 0))
);


ALTER TABLE public.c_terms_condition OWNER TO postgres;

--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection character varying(255) NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: menu_role; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.menu_role (
    id character varying(36) DEFAULT public.uuid_generate_v4() NOT NULL,
    menu_id character varying(36),
    role_id character varying(36),
    permission character varying(50),
    created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.menu_role OWNER TO postgres;

--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: t_booking; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.t_booking (
    id character varying(36) DEFAULT (public.uuid_generate_v4())::character varying NOT NULL,
    booking_code character varying(30) NOT NULL,
    customer_id character varying(36) NOT NULL,
    rental_package_id character varying(36) NOT NULL,
    playstation_unit_id character varying(36),
    terms_condition_id character varying(36),
    rental_start_at timestamp without time zone NOT NULL,
    rental_end_at timestamp without time zone NOT NULL,
    delivery_address text NOT NULL,
    google_place_id character varying(255),
    latitude numeric(10,7),
    longitude numeric(10,7),
    distance_km numeric(8,2) DEFAULT 0,
    package_price numeric(12,2) DEFAULT 0 NOT NULL,
    delivery_fee numeric(12,2) DEFAULT 0 NOT NULL,
    discount_amount numeric(12,2) DEFAULT 0 NOT NULL,
    total_amount numeric(12,2) DEFAULT 0 NOT NULL,
    payment_option character varying(20),
    initial_payment_amount numeric(12,2) DEFAULT 0 NOT NULL,
    total_paid numeric(12,2) DEFAULT 0 NOT NULL,
    remaining_amount numeric(12,2) DEFAULT 0 NOT NULL,
    payment_status character varying(30) DEFAULT 'unpaid'::character varying,
    booking_status character varying(30) DEFAULT 'pending_payment'::character varying,
    terms_accepted boolean DEFAULT false,
    terms_accepted_at timestamp without time zone,
    delivery_started_at timestamp without time zone,
    arrived_at timestamp without time zone,
    delivered_at timestamp without time zone,
    customer_notes text,
    cancellation_reason text,
    created_by character varying(36),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by character varying(36),
    updated_at timestamp without time zone,
    is_deleted boolean DEFAULT false,
    driver_id character varying(36),
    vehicle_type character varying(20),
    driver_fee numeric(12,2) DEFAULT 0,
    fuel_deduction numeric(12,2) DEFAULT 0,
    driver_income numeric(12,2) DEFAULT 0,
    driver_assigned_at timestamp without time zone,
    delivery_photo_path character varying(500),
    delivery_photo_taken_at timestamp without time zone,
    pickup_started_at timestamp without time zone,
    picked_up_at timestamp without time zone,
    CONSTRAINT booking_remaining_amount_check CHECK ((remaining_amount >= (0)::numeric)),
    CONSTRAINT booking_rental_time_check CHECK ((rental_end_at > rental_start_at)),
    CONSTRAINT booking_total_amount_check CHECK ((total_amount >= (0)::numeric)),
    CONSTRAINT booking_total_paid_check CHECK ((total_paid >= (0)::numeric))
);


ALTER TABLE public.t_booking OWNER TO postgres;

--
-- Name: t_booking_idempotency; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.t_booking_idempotency (
    token character varying(36) NOT NULL,
    payment_code character varying(50) NOT NULL,
    booking_id character varying(36),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.t_booking_idempotency OWNER TO postgres;

--
-- Name: t_payment; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.t_payment (
    id character varying(36) DEFAULT (public.uuid_generate_v4())::character varying NOT NULL,
    booking_id character varying(36) NOT NULL,
    payment_code character varying(50) NOT NULL,
    payment_type character varying(20) NOT NULL,
    payment_method character varying(30) DEFAULT 'qris'::character varying,
    provider character varying(30),
    external_payment_id character varying(255),
    external_reference_id character varying(255),
    requested_amount numeric(12,2) DEFAULT 0 NOT NULL,
    paid_amount numeric(12,2) DEFAULT 0 NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying,
    qr_string text,
    qr_url text,
    expires_at timestamp without time zone,
    paid_at timestamp without time zone,
    created_by character varying(36),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by character varying(36),
    updated_at timestamp without time zone,
    is_deleted boolean DEFAULT false,
    CONSTRAINT payment_paid_amount_check CHECK ((paid_amount >= (0)::numeric)),
    CONSTRAINT payment_requested_amount_check CHECK ((requested_amount >= (0)::numeric))
);


ALTER TABLE public.t_payment OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: auth_role; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.auth_role (id, name, description) FROM stdin;
f234c2ae-13d4-4c21-a2c9-6379247ed9ed	Admin	Memiliki akses penuh untuk mengelola sistem Maxibox Playstation.
649bbf60-f937-41ab-9355-f44cc895d088	Driver	Bertugas mengantar dan mengambil unit PlayStation dari customer.
\.


--
-- Data for Name: auth_user; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.auth_user (id, name, username, email, password, role_id, url_photo, active, last_login, created_by, created_at, updated_by, updated_at, deleted_at, is_deleted, status) FROM stdin;
87d61fef-ecda-4250-aabd-2d7e17de2ed1	Morena Rafaela	driver2	driver2@gmail.com	$2y$12$J7kxIx9.AzpYy8AL.Gmc3OZNelOj7mQl6cgGOUNutY2gidCaF8Ob.	649bbf60-f937-41ab-9355-f44cc895d088	\N	t	\N	fcd2034c-81a9-4096-b964-0802abd17735	2026-09-29 08:51:39	\N	2026-09-29 08:51:39	\N	f	\N
fcd2034c-81a9-4096-b964-0802abd17735	Administrator Maxibox	admin	admin@maxibox.com	$2y$12$I53RYjCjy6ZqS9WhwqRL4OMzMtscYuwm6rRseS4jzGE52cisx0qES	f234c2ae-13d4-4c21-a2c9-6379247ed9ed	\N	t	2026-10-02 13:57:54	\N	2026-08-12 19:59:27.418414	\N	\N	\N	f	1
84e2e75b-657e-4f25-b9a2-f0e3ccc193a6	Romano Gio	driver3	driver3@gmail.com	$2y$12$lB7aH9isspCCWwQexhgCn.OXJMWwJPKy7CsVpH7BFKFPjtmwxVnTS	649bbf60-f937-41ab-9355-f44cc895d088	\N	t	2026-10-02 14:14:19	fcd2034c-81a9-4096-b964-0802abd17735	2026-10-02 14:14:05	\N	2026-10-02 14:14:05	\N	f	\N
9ae657a0-d924-4c64-95ba-849d6eafc4bb	Alexa Chandra	driver	driver@maxibox.com	$2y$12$h0yFFTB0nomlt3BLlm./I.Sb1VXoUVnCJtlAVbXPSmWTYtUpJeumW	649bbf60-f937-41ab-9355-f44cc895d088	\N	t	2026-10-04 20:11:55	\N	2026-08-12 19:59:55.567273	\N	\N	\N	f	1
\.


--
-- Data for Name: c_business_setting; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.c_business_setting (id, business_name, phone_number, address, google_place_id, latitude, longitude, down_payment_amount, payment_expiry_minutes, maximum_delivery_km, is_active, created_by, created_at, updated_by, updated_at, is_deleted) FROM stdin;
7b41910b-062f-4a5d-a89e-f4f34bf3dd8a	Maxibox Playstation	081339900234	Jl. Asparaga Gamping Tengah, Gamping Tengah, Gamping, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur 61262	\N	-7.4158258	112.5918429	50000.00	60	1000.00	t	\N	2026-08-12 20:18:44.676537	fcd2034c-81a9-4096-b964-0802abd17735	2026-09-03 02:46:31	f
\.


--
-- Data for Name: c_customer; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.c_customer (id, full_name, phone_number, created_by, created_at, updated_by, updated_at, is_deleted) FROM stdin;
11a2daa3-2da9-4a33-8137-84cb82f0a686	Raasikh Test	6285895108722	\N	2026-08-23 09:22:22	\N	2026-08-23 09:22:22	f
f09b2263-bc74-43d7-a07e-cf3e9c815ad1	Rskh Test	6285777999222	\N	2026-08-30 11:36:29	\N	2026-08-30 11:36:29	f
eda7817b-e4be-48c4-8897-7e1b9531e12f	haloo	6285666444555	\N	2026-08-30 11:43:49	\N	2026-08-30 11:43:49	f
17b13255-993c-4238-a87f-d26ad55f2aa1	haiii	6285222888999	\N	2026-08-30 11:48:27	\N	2026-08-30 11:48:27	f
fa6f377b-9843-4450-b1b9-bef45df81ca7	Romano	628666777555	\N	2026-08-31 08:45:04	\N	2026-08-31 08:45:04	f
93aef07b-027b-4755-83e2-29cd8db255b2	Tiara	628765876567	\N	2026-08-31 08:57:44	\N	2026-08-31 08:57:44	f
e93680d6-5197-4e60-818b-e3d4771c5a46	Rakhaa 1	6285858585855	\N	2026-09-18 16:50:41	\N	2026-09-18 16:50:41	f
91aa7b5a-c339-4ca8-ba1f-cd1351982270	siapa lagi	6298765432567	\N	2026-09-29 07:58:44	\N	2026-09-29 07:58:44	f
7c8f4bd9-445a-42c0-b291-ff6de8f0e701	apalagi	6287985756464	\N	2026-09-29 08:10:13	\N	2026-09-29 08:10:13	f
0e88645a-aec6-4e1a-b0b5-3af1292c4d09	mekdi	6289767876789	\N	2026-09-29 08:19:01	\N	2026-09-29 08:19:01	f
18688cbf-117e-4ed9-a051-d06ea143dea2	choki	6285675675674	\N	2026-09-29 08:38:44	\N	2026-09-29 08:38:44	f
ca9901e3-1524-45cc-bb7b-2fb8d466cf27	rakha alden	6287678678678	\N	2026-10-02 13:57:36	\N	2026-10-02 13:57:36	f
273cbd50-b0b4-46b5-9b6c-e9366ec3ce5b	Raasikh Test	6289989898989	\N	2026-10-02 14:06:27	\N	2026-10-02 14:06:27	f
b87c5ee8-f25e-4031-b8ef-8702e2b452c6	finishing 1	6285111111111	\N	2026-10-04 09:31:31	\N	2026-10-04 09:31:31	f
8ae087a8-48d7-4702-8a7b-bd005a45da29	finishing 2	6285111111112	\N	2026-10-04 09:33:42	\N	2026-10-04 09:33:42	f
09d8c537-aa7c-42cb-bf11-d77195df3c57	finishing 3	6285111111113	\N	2026-10-04 09:52:39	\N	2026-10-04 09:52:39	f
e4a45994-442b-4bb9-83e9-0e0b1e9bf319	finishing 4	6285111111114	\N	2026-10-04 10:00:53	\N	2026-10-04 10:00:53	f
426c958e-2bc1-43d0-a3fb-a3c07042b880	transaksi dp	6285222222222	\N	2026-10-04 10:09:10	\N	2026-10-04 10:09:10	f
652008b7-7030-4265-889a-792238107601	tes expired	6285333333333	\N	2026-10-04 10:17:46	\N	2026-10-04 10:17:46	f
2acd9315-4c84-4c3e-a9dc-023f7db56f99	tes sama	6281777777777	\N	2026-10-04 20:18:36	\N	2026-10-04 20:18:36	f
3287ebba-cce2-4675-a630-560433567874	tes sama 2	6285444333444	\N	2026-10-04 21:23:57	\N	2026-10-04 21:23:57	f
5195cd8d-914e-43ea-928e-055bb4d0af45	tes sama 3	6285777888777	\N	2026-10-04 21:33:42	\N	2026-10-04 21:33:42	f
f2b3f15d-d6ec-4beb-8659-8c18e882d805	Romeo	6285678465264	\N	2026-09-03 01:59:08	\N	2026-09-03 01:59:08	f
ef262d86-f2ac-4196-b56a-1517d07baa76	Rakha	6285678534623	\N	2026-09-03 02:14:57	\N	2026-09-03 02:14:57	f
\.


--
-- Data for Name: c_delivery_rate; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.c_delivery_rate (id, name, minimum_distance_km, maximum_distance_km, delivery_fee, is_active, created_by, created_at, updated_by, updated_at, is_deleted, driver_fee, company_fuel_deduction) FROM stdin;
4b397e9d-a40b-49b6-8bb4-8eab4b61064c	1–2 km	0.00	2.00	0.00	t	\N	2026-09-29 11:12:10.433243	\N	2026-09-29 11:12:10.433243	f	10000.00	0.00
3d2ab6a9-d5d2-402f-b222-38816d58aae5	3–4 km	2.00	4.00	20000.00	t	\N	2026-09-29 11:12:10.433243	\N	2026-09-29 11:12:10.433243	f	20000.00	10000.00
eb436b64-e344-4b15-9f03-8e330f40bfa4	5–6 km	4.00	6.00	25000.00	t	\N	2026-09-29 11:12:10.433243	\N	2026-09-29 11:12:10.433243	f	25000.00	10000.00
1746bc35-4bf6-472d-8702-237a47267ba4	7–8 km	6.00	8.00	30000.00	t	\N	2026-09-29 11:12:10.433243	\N	2026-09-29 11:12:10.433243	f	30000.00	15000.00
05453088-f5c3-476d-84cd-2b4ff80d8625	9–10 km	8.00	10.00	35000.00	t	\N	2026-09-29 11:12:10.433243	\N	2026-09-29 11:12:10.433243	f	35000.00	15000.00
5f807958-a2d9-4ee7-bb99-e9e335dffedd	11–12 km	10.00	12.00	40000.00	t	\N	2026-09-29 11:12:10.433243	\N	2026-09-29 11:12:10.433243	f	40000.00	15000.00
7165d553-8aca-4a74-a581-fb11ab7a8a71	13–14 km	12.00	14.00	45000.00	t	\N	2026-09-29 11:12:10.433243	\N	2026-09-29 11:12:10.433243	f	45000.00	20000.00
9623dc0e-af78-4960-8506-4630c153bc2e	15–16 km	14.00	16.00	50000.00	t	\N	2026-09-29 11:12:10.433243	\N	2026-09-29 11:12:10.433243	f	50000.00	20000.00
5f7b9469-9680-429d-af23-e7a25cdaea02	17–18 km	16.00	18.00	60000.00	t	\N	2026-09-29 11:12:10.433243	\N	2026-09-29 11:12:10.433243	f	60000.00	25000.00
adddd87e-51fb-40f1-9e9b-8b4c766f4631	Zona 1	0.00	3.00	10000.00	f	\N	2026-08-12 20:18:44.676537	fcd2034c-81a9-4096-b964-0802abd17735	2026-09-29 08:58:14	t	0.00	0.00
8f8f6c5c-2051-477f-945e-22426ca8c5f6	Zona 2	3.00	7.00	20000.00	f	\N	2026-08-12 20:18:44.676537	fcd2034c-81a9-4096-b964-0802abd17735	2026-09-29 08:58:17	t	0.00	0.00
0bc9ecd1-aa18-41ee-a177-70b7b725263d	Zona 3	7.00	12.00	30000.00	f	\N	2026-08-12 20:18:44.676537	fcd2034c-81a9-4096-b964-0802abd17735	2026-09-29 08:58:23	t	0.00	0.00
29b1e29e-34cc-4bdb-a819-a94862b3a9b7	Zona 4	12.00	20.00	40000.00	f	\N	2026-08-12 20:18:44.676537	fcd2034c-81a9-4096-b964-0802abd17735	2026-09-29 08:58:25	t	0.00	0.00
\.


--
-- Data for Name: c_faq; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.c_faq (id, question, answer, seq, is_published, created_by, created_at, updated_by, updated_at, is_deleted) FROM stdin;
75667552-5dae-4e7d-ab1a-635162a4a206	Apakah bisa membayar uang muka?	Bisa. Customer dapat membayar uang muka sebesar Rp50.000 dan melunasi sisanya saat PlayStation sampai.	2	t	\N	2026-08-12 20:18:44.676537	\N	\N	f
38f0e365-ca38-46b8-9287-cb7571495fda	Berapa lama QRIS pembayaran berlaku?	QRIS pembayaran awal berlaku selama 60 menit sejak pesanan dibuat.	3	t	\N	2026-08-12 20:18:44.676537	\N	\N	f
704469b6-ddef-486a-9415-0b43d84a5329	Apakah PlayStation diantar ke rumah?	Ya. PlayStation akan diantar ke alamat customer selama masih berada dalam jangkauan pengiriman Maxibox Playstation.	4	t	\N	2026-08-12 20:18:44.676537	\N	\N	f
3a26eb7c-c285-413b-9722-20af56910f77	Bagaimana biaya pengiriman dihitung?	Biaya pengiriman dihitung berdasarkan jarak perjalanan dari lokasi Maxibox Playstation ke alamat customer.	5	t	\N	2026-08-12 20:18:44.676537	\N	\N	f
2526d085-48f4-4bdd-ac13-5ad2942c8570	Kapan sisa pembayaran harus dilunasi?	Sisa pembayaran wajib langsung dilunasi ketika PlayStation sudah sampai di lokasi customer.	6	t	\N	2026-08-12 20:18:44.676537	\N	\N	f
6645ba47-cd61-4f63-9fc8-3865edbccf87	Apakah pesanan dapat dibatalkan?	Pembatalan mengikuti syarat dan ketentuan Maxibox Playstation. Hubungi admin untuk informasi lebih lanjut.	7	t	\N	2026-08-12 20:18:44.676537	\N	\N	f
0bb95fdd-8a62-4345-b331-2ee0ca9e4b2f	Bagaimana cara memesan PlayStation?	Pilih menu Pesan, lengkapi data penyewaan dan alamat, lalu lakukan pembayaran menggunakan QRIS.	1	t	\N	2026-08-12 20:18:44.676537	fcd2034c-81a9-4096-b964-0802abd17735	2026-09-03 03:36:31	f
\.


--
-- Data for Name: c_menu; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.c_menu (id, name, link, icon, description, permission_label, action, level, seq, created_by, created_at, updated_by, updated_at, is_deleted, parent_id) FROM stdin;
\.


--
-- Data for Name: c_playstation_unit; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.c_playstation_unit (id, unit_code, name, console_type, serial_number, status, notes, is_active, created_by, created_at, updated_by, updated_at, is_deleted) FROM stdin;
57f8b7d9-d5cd-4f75-b55f-22d0663ed416	PS-001	PlayStation Unit 1	PS4	\N	available	Unit PlayStation siap disewakan.	t	\N	2026-08-12 20:18:44.676537	fcd2034c-81a9-4096-b964-0802abd17735	2026-10-02 14:11:43	f
b3e382bc-2ad3-4ef5-84d2-524ea7819121	PS-002	PlayStation Unit 2	PS4	\N	available	Unit PlayStation siap disewakan.	t	\N	2026-08-12 20:18:44.676537	fcd2034c-81a9-4096-b964-0802abd17735	2026-10-02 14:11:52	f
\.


--
-- Data for Name: c_rental_package; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.c_rental_package (id, code, name, duration_hours, price, blocked_start_time, blocked_end_time, description, is_active, created_by, created_at, updated_by, updated_at, is_deleted) FROM stdin;
8851c032-b8a4-4335-9245-96bce17e2077	RENT-12H	Paket 12 Jam	12	120000.00	13:00:00	19:00:00	Penyewaan PlayStation selama 12 jam. Pemesanan tidak tersedia mulai pukul 13.00 sampai 19.00.	t	\N	2026-08-12 20:18:44.676537	\N	\N	f
82652bf1-5e26-4f49-8853-1a68dd9181de	RENT-24H	Paket 24 Jam	24	180000.00	\N	\N	Penyewaan PlayStation selama 24 jam.	t	\N	2026-08-12 20:18:44.676537	\N	\N	f
98f9b9d7-4382-45c0-b23d-ea2a097578e3	RENT-6H	Paket 6 Jam	6	70000.00	19:00:00	23:59:00	Penyewaan PlayStation selama 6 jam. Pemesanan tidak tersedia mulai pukul 15.00 sampai 24.00.	t	\N	2026-08-12 20:18:44.676537	fcd2034c-81a9-4096-b964-0802abd17735	2026-09-18 17:02:23	f
\.


--
-- Data for Name: c_terms_condition; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.c_terms_condition (id, title, content, version, is_active, created_by, created_at, updated_by, updated_at, is_deleted) FROM stdin;
24359e0f-adb4-4efc-bc75-eb139123c876	Syarat dan Ketentuan Penyewaan	1. Customer wajib mengisi data pemesanan dengan benar.\r\n2. Customer dapat memilih pembayaran lunas atau uang muka sebesar Rp50.000.\r\n3. Uang muka digunakan untuk mengunci jadwal penyewaan.\r\n4. Jika memilih uang muka, sisa tagihan wajib dilunasi saat PlayStation sampai di lokasi customer.\r\n5. PlayStation hanya diserahkan setelah pembayaran dinyatakan lunas.\r\n6. QRIS pembayaran awal berlaku selama 60 menit.\r\n7. Pesanan akan dibatalkan otomatis apabila pembayaran awal tidak diterima dalam 60 menit.\r\n8. Customer bertanggung jawab menjaga unit dan perlengkapan selama masa penyewaan.\r\n9. Kerusakan atau kehilangan akibat kelalaian customer menjadi tanggung jawab customer.\r\n10. Dengan mencentang persetujuan, customer dianggap memahami dan menyetujui seluruh ketentuan.	1	t	\N	2026-08-12 20:18:44.676537	fcd2034c-81a9-4096-b964-0802abd17735	2026-09-03 02:49:17	f
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: menu_role; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.menu_role (id, menu_id, role_id, permission, created_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_10_04_205430_create_t_booking_idempotency_table	1
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
\.


--
-- Data for Name: t_booking; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.t_booking (id, booking_code, customer_id, rental_package_id, playstation_unit_id, terms_condition_id, rental_start_at, rental_end_at, delivery_address, google_place_id, latitude, longitude, distance_km, package_price, delivery_fee, discount_amount, total_amount, payment_option, initial_payment_amount, total_paid, remaining_amount, payment_status, booking_status, terms_accepted, terms_accepted_at, delivery_started_at, arrived_at, delivered_at, customer_notes, cancellation_reason, created_by, created_at, updated_by, updated_at, is_deleted, driver_id, vehicle_type, driver_fee, fuel_deduction, driver_income, driver_assigned_at, delivery_photo_path, delivery_photo_taken_at, pickup_started_at, picked_up_at) FROM stdin;
fc3c9f22-76e0-4bad-bcc2-0b931a1a392c	MXB-20260929-BBWX	7c8f4bd9-445a-42c0-b291-ff6de8f0e701	82652bf1-5e26-4f49-8853-1a68dd9181de	b3e382bc-2ad3-4ef5-84d2-524ea7819121	24359e0f-adb4-4efc-bc75-eb139123c876	2026-09-29 15:10:00	2026-09-30 15:10:00	Lebo, Kec. Sidoarjo, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJb1xOygrh1y0R7OOFszGPw50	-7.4547744	112.6705194	11.02	180000.00	40000.00	0.00	220000.00	full	220000.00	220000.00	0.00	paid	completed	t	2026-09-29 08:10:16	2026-09-29 08:11:27	2026-09-29 08:12:49	2026-09-29 08:12:49	rumah kuning	\N	\N	2026-09-29 08:10:16	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-02 14:03:59	f	9ae657a0-d924-4c64-95ba-849d6eafc4bb	company	40000.00	15000.00	25000.00	2026-09-29 08:11:27	delivery-proofs/2026/09/lDPux8dFmUnNR7nUoGnyem81kLv5EX01S7EltfDN.png	2026-09-29 08:12:49	2026-10-02 14:03:59	2026-10-02 14:03:59
536545a9-763e-4d8e-a4fd-0eb6a8833f59	MXB-20260929-6JTB	91aa7b5a-c339-4ca8-ba1f-cd1351982270	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-09-30 14:59:00	2026-10-01 14:59:00	Pilang, Kec. Wonoayu, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJ2c6Zjpnh1y0R5N2edPGt5ME	-7.4453141	112.6586009	9.02	180000.00	35000.00	0.00	215000.00	full	215000.00	215000.00	0.00	paid	delivered	t	2026-09-29 07:58:44	2026-09-29 08:00:14	\N	\N	rumah oren	\N	\N	2026-09-29 07:58:44	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-09-29 08:00:14	t	9ae657a0-d924-4c64-95ba-849d6eafc4bb	company	35000.00	15000.00	20000.00	2026-09-29 08:00:14	\N	\N	\N	\N
3f791c10-d841-40e2-97e5-fb0df415b475	MXB-20260918-00SR	e93680d6-5197-4e60-818b-e3d4771c5a46	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-09-20 23:49:00	2026-09-21 23:49:00	Tanggul, Kec. Wonoayu, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJCaz_i4oJeC4R7Lrs3_6FXvI	-7.4336705	112.5930470	3.52	180000.00	20000.00	0.00	200000.00	deposit	50000.00	200000.00	0.00	paid	arrived	t	2026-09-18 16:50:41	\N	2026-09-18 16:57:45	\N	\N	\N	\N	2026-09-18 16:50:41	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-09-18 16:57:45	t	\N	\N	0.00	0.00	0.00	\N	\N	\N	\N	\N
029b91a7-b6ef-47f3-b64b-fdce8fd74db2	MXB-20260831-TKDT	93aef07b-027b-4755-83e2-29cd8db255b2	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-09-03 15:58:00	2026-09-04 15:58:00	Tanggul, Kec. Wonoayu, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJCaz_i4oJeC4R7Lrs3_6FXvI	-7.4336705	112.5930470	3.52	180000.00	20000.00	0.00	200000.00	deposit	50000.00	200000.00	0.00	paid	delivered	t	2026-08-31 08:57:45	\N	\N	\N	rumah oren biru	\N	\N	2026-08-31 08:57:45	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-09-03 02:00:39	t	\N	\N	0.00	0.00	0.00	\N	\N	\N	\N	\N
112d6c11-f0de-48bb-9eda-eeeee01ad383	MXB-20260903-U3OQ	f2b3f15d-d6ec-4beb-8659-8c18e882d805	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-09-05 08:59:00	2026-09-06 08:59:00	Tanggul, Kec. Wonoayu, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJCaz_i4oJeC4R7Lrs3_6FXvI	-7.4336705	112.5930470	3.52	180000.00	20000.00	0.00	200000.00	deposit	50000.00	200000.00	0.00	paid	arrived	t	2026-09-03 01:59:09	\N	2026-09-03 02:00:49	\N	rumah putih	\N	\N	2026-09-03 01:59:09	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-09-03 02:00:49	t	\N	\N	0.00	0.00	0.00	\N	\N	\N	\N	\N
9a16e4fc-14d6-40b8-aa8a-feb792751b39	MXB-20260903-T2NQ	ef262d86-f2ac-4196-b56a-1517d07baa76	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-09-07 13:15:00	2026-09-08 13:15:00	Jl. Kyai Mojo, Dusun Jeruk, Jerukgamping, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur 61262, Indonesia	ChIJawUpwZMJeC4R1MFUZs2liuA	-7.4204349	112.5888673	2.16	180000.00	10000.00	0.00	190000.00	deposit	50000.00	190000.00	0.00	paid	arrived	t	2026-09-03 02:14:57	\N	2026-09-03 02:16:47	\N	rumah putih	\N	\N	2026-09-03 02:14:57	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-09-03 02:16:47	t	\N	\N	0.00	0.00	0.00	\N	\N	\N	\N	\N
95d4df6a-81ef-40c3-98fa-fff2d1dfe33f	MXB-20260830-1WPP	17b13255-993c-4238-a87f-d26ad55f2aa1	98f9b9d7-4382-45c0-b23d-ea2a097578e3	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-09-02 09:47:00	2026-09-02 15:47:00	Tanggul, Kec. Wonoayu, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJCaz_i4oJeC4R7Lrs3_6FXvI	-7.4336705	112.5930470	3.52	70000.00	20000.00	0.00	90000.00	full	90000.00	0.00	0.00	unpaid	canceled	t	2026-08-30 11:48:27	\N	\N	\N	rumah oren	\N	\N	2026-08-30 11:48:27	fcd2034c-81a9-4096-b964-0802abd17735	2026-08-31 04:38:11	t	\N	\N	0.00	0.00	0.00	\N	\N	\N	\N	\N
0d10d225-76d6-48c1-bfe7-3216be73a816	MXB-20260830-JDN3	eda7817b-e4be-48c4-8897-7e1b9531e12f	98f9b9d7-4382-45c0-b23d-ea2a097578e3	b3e382bc-2ad3-4ef5-84d2-524ea7819121	24359e0f-adb4-4efc-bc75-eb139123c876	2026-08-31 08:43:00	2026-08-31 14:43:00	Tanggul, Kec. Wonoayu, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJCaz_i4oJeC4R7Lrs3_6FXvI	-7.4336705	112.5930470	3.52	70000.00	20000.00	0.00	90000.00	full	90000.00	0.00	0.00	unpaid	canceled	t	2026-08-30 11:43:49	\N	\N	\N	ruah putih	\N	\N	2026-08-30 11:43:49	fcd2034c-81a9-4096-b964-0802abd17735	2026-08-31 04:38:30	t	\N	\N	0.00	0.00	0.00	\N	\N	\N	\N	\N
f9bf61c1-4efa-40f2-8d22-59e039291e39	MXB-20260830-JVK9	f09b2263-bc74-43d7-a07e-cf3e9c815ad1	98f9b9d7-4382-45c0-b23d-ea2a097578e3	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-08-31 11:00:00	2026-08-31 17:00:00	Jl. Kyai Mojo, Dusun Jeruk, Jerukgamping, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur 61262, Indonesia	ChIJawUpwZMJeC4R1MFUZs2liuA	-7.4204349	112.5888673	2.16	70000.00	10000.00	0.00	80000.00	full	80000.00	0.00	0.00	unpaid	canceled	t	2026-08-30 11:36:29	\N	\N	\N	rumah warna oren	\N	\N	2026-08-30 11:36:29	fcd2034c-81a9-4096-b964-0802abd17735	2026-08-31 04:38:48	t	\N	\N	0.00	0.00	0.00	\N	\N	\N	\N	\N
926603fd-119a-4a4a-aee7-32349b014771	MXB-20260823-VGWJ	11a2daa3-2da9-4a33-8137-84cb82f0a686	8851c032-b8a4-4335-9245-96bce17e2077	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-08-23 10:00:00	2026-08-23 22:00:00	Jl. Raya Tanggul No.310, Tanggul Wetan, Tanggul, Kec. Wonoayu, Kabupaten Sidoarjo, Jawa Timur 61261, Indonesia	ChIJI940HI8JeC4RI5uKSSW-p18	-7.4346784	112.5960290	3.84	120000.00	20000.00	0.00	140000.00	full	140000.00	0.00	0.00	unpaid	canceled	t	2026-08-23 09:22:23	\N	\N	\N	\N	\N	\N	2026-08-23 09:22:23	fcd2034c-81a9-4096-b964-0802abd17735	2026-08-31 04:41:53	t	\N	\N	0.00	0.00	0.00	\N	\N	\N	\N	\N
73268278-fc88-4309-b0b4-8325884ab9e5	MXB-20260831-CVAE	fa6f377b-9843-4450-b1b9-bef45df81ca7	8851c032-b8a4-4335-9245-96bce17e2077	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-09-02 03:45:00	2026-09-02 15:45:00	Jl. Kyai Mojo, Dusun Jeruk, Jerukgamping, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur 61262, Indonesia	ChIJawUpwZMJeC4R1MFUZs2liuA	-7.4204349	112.5888673	2.16	120000.00	10000.00	0.00	130000.00	full	130000.00	130000.00	0.00	paid	arrived	t	2026-08-31 08:45:04	\N	2026-08-31 08:56:20	\N	rumah oren	\N	\N	2026-08-31 08:45:04	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-08-31 08:56:20	t	\N	\N	0.00	0.00	0.00	\N	\N	\N	\N	\N
b6f485fd-816b-4b4e-bd6d-66cfa1c4c9e4	MXB-20260929-IHVJ	18688cbf-117e-4ed9-a051-d06ea143dea2	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-07 15:38:00	2026-10-08 15:38:00	Kec. Prambon, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJa8taak4KeC4RJSOYNw0cYfU	-7.4457506	112.5707595	5.77	180000.00	25000.00	0.00	205000.00	full	205000.00	205000.00	0.00	paid	arrived	t	2026-09-29 08:38:44	2026-09-29 08:39:28	2026-09-29 08:39:54	2026-09-29 08:39:54	rumah hijau botol	\N	\N	2026-09-29 08:38:44	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-09-29 08:39:54	f	9ae657a0-d924-4c64-95ba-849d6eafc4bb	company	25000.00	10000.00	15000.00	2026-09-29 08:39:28	delivery-proofs/2026/09/dpfN698GqQcP8wUrUFlV861kQv5xFfvvd7UHMMP5.png	2026-09-29 08:39:54	\N	\N
920a16f3-58b3-4757-9dc5-3077cb20dd91	MXB-20260929-UHOG	0e88645a-aec6-4e1a-b0b5-3af1292c4d09	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-03 15:20:00	2026-10-04 15:20:00	Pilang, Kec. Wonoayu, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJ2c6Zjpnh1y0R5N2edPGt5ME	-7.4453141	112.6586009	9.02	180000.00	35000.00	0.00	215000.00	full	215000.00	215000.00	0.00	paid	arrived	t	2026-09-29 08:19:02	2026-09-29 08:19:47	2026-09-29 08:29:57	2026-09-29 08:29:57	rumah putih	\N	\N	2026-09-29 08:19:02	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-09-29 08:29:57	f	9ae657a0-d924-4c64-95ba-849d6eafc4bb	personal	35000.00	0.00	35000.00	2026-09-29 08:19:47	delivery-proofs/2026/09/EYgoVyzkaufyvWMb5FNIVsCstbhgv8DVlWzsRx2s.png	2026-09-29 08:29:57	\N	\N
269f5953-015b-42e7-a33e-0959c0761931	MXB-20261002-OMTM	ca9901e3-1524-45cc-bb7b-2fb8d466cf27	82652bf1-5e26-4f49-8853-1a68dd9181de	b3e382bc-2ad3-4ef5-84d2-524ea7819121	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-06 20:58:00	2026-10-07 20:58:00	Tanggul, Kec. Wonoayu, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJCaz_i4oJeC4R7Lrs3_6FXvI	-7.4336705	112.5930470	3.52	180000.00	20000.00	0.00	200000.00	full	200000.00	200000.00	0.00	paid	arrived	t	2026-10-02 13:57:37	2026-10-02 14:02:27	2026-10-02 14:03:00	2026-10-02 14:03:00	rumah putih	\N	\N	2026-10-02 13:57:37	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-02 14:03:00	f	9ae657a0-d924-4c64-95ba-849d6eafc4bb	personal	20000.00	0.00	20000.00	2026-10-02 14:02:27	delivery-proofs/2026/10/XpctNqFoWLLcM8XpdCBfEmN1qWNTye7D4dYgAjyE.png	2026-10-02 14:03:00	\N	\N
22442282-ec53-4354-b7f3-b000fe4beab8	MXB-20261002-UJIO	273cbd50-b0b4-46b5-9b6c-e9366ec3ce5b	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-22 21:07:00	2026-10-23 21:07:00	Sadenganmijen, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJuXc-5o4JeC4Roknt0NaMn1k	-7.4277543	112.5855974	2.85	180000.00	20000.00	0.00	200000.00	full	200000.00	200000.00	0.00	paid	on_delivery	t	2026-10-02 14:06:27	2026-10-02 14:09:55	\N	\N	rumah abu abu	\N	\N	2026-10-02 14:06:27	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-02 14:09:55	f	9ae657a0-d924-4c64-95ba-849d6eafc4bb	company	20000.00	10000.00	10000.00	2026-10-02 14:09:55	\N	\N	\N	\N
453e5e77-6e62-4569-a5d5-f395ec943e98	MXB-20261004-JNNR	b87c5ee8-f25e-4031-b8ef-8702e2b452c6	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-04 16:31:00	2026-10-05 16:31:00	Tanggul, Kec. Wonoayu, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJCaz_i4oJeC4R7Lrs3_6FXvI	-7.4336705	112.5930470	3.52	180000.00	20000.00	0.00	200000.00	full	200000.00	200000.00	0.00	paid	arrived	t	2026-10-04 09:31:31	2026-10-04 17:23:37	2026-10-04 17:23:50	2026-10-04 17:23:50	rumah bagus	\N	\N	2026-10-04 09:31:31	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-04 17:23:50	f	9ae657a0-d924-4c64-95ba-849d6eafc4bb	company	20000.00	10000.00	10000.00	2026-10-04 17:23:37	delivery-proofs/2026/10/oqlvwkWLjoLOvidoCOakjoPWrwZidMvC4mTIQz9H.png	2026-10-04 17:23:50	\N	\N
51e560d5-44ef-42ab-8991-13e8b6ad5006	MXB-20261004-AIWA	8ae087a8-48d7-4702-8a7b-bd005a45da29	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-25 16:33:00	2026-10-26 16:33:00	Sadenganmijen, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJuXc-5o4JeC4Roknt0NaMn1k	-7.4277543	112.5855974	2.85	180000.00	20000.00	0.00	200000.00	deposit	50000.00	200000.00	0.00	paid	on_delivery	t	2026-10-04 09:33:42	2026-10-04 09:34:39	\N	\N	rumah jelek	\N	\N	2026-10-04 09:33:42	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-04 09:41:47	f	9ae657a0-d924-4c64-95ba-849d6eafc4bb	personal	20000.00	0.00	20000.00	2026-10-04 09:34:39	\N	\N	\N	\N
6cec40b2-a8c9-45ac-82b0-eefc14112789	MXB-20261004-TWHY	09d8c537-aa7c-42cb-bf11-d77195df3c57	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-10 16:52:00	2026-10-11 16:52:00	Sadenganmijen, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJuXc-5o4JeC4Roknt0NaMn1k	-7.4277543	112.5855974	2.85	180000.00	20000.00	0.00	200000.00	deposit	50000.00	200000.00	0.00	paid	on_delivery	t	2026-10-04 09:52:39	2026-10-04 09:58:38	\N	\N	\N	\N	\N	2026-10-04 09:52:39	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-04 09:59:29	f	9ae657a0-d924-4c64-95ba-849d6eafc4bb	personal	20000.00	0.00	20000.00	2026-10-04 09:58:38	\N	\N	\N	\N
94738909-2388-45de-b517-e47d56f05451	MXB-20261004-VATV	2acd9315-4c84-4c3e-a9dc-023f7db56f99	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-12 20:17:00	2026-10-13 20:17:00	Jl. Kyai Mojo, Dusun Jeruk, Jerukgamping, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur 61262, Indonesia	ChIJawUpwZMJeC4R1MFUZs2liuA	-7.4198583	112.5906420	2.46	180000.00	20000.00	0.00	200000.00	deposit	50000.00	0.00	150000.00	unpaid	expired	t	2026-10-04 20:18:42	\N	\N	\N	tes booking sama	\N	\N	2026-10-04 20:18:42	\N	2026-10-04 21:20:00	f	\N	\N	20000.00	10000.00	0.00	\N	\N	\N	\N	\N
69a386f5-8483-4f5c-a4f5-0a035a9a5c60	MXB-20261004-NSSB	2acd9315-4c84-4c3e-a9dc-023f7db56f99	82652bf1-5e26-4f49-8853-1a68dd9181de	b3e382bc-2ad3-4ef5-84d2-524ea7819121	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-12 20:17:00	2026-10-13 20:17:00	Jl. Kyai Mojo, Dusun Jeruk, Jerukgamping, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur 61262, Indonesia	ChIJawUpwZMJeC4R1MFUZs2liuA	-7.4198583	112.5906420	2.46	180000.00	20000.00	0.00	200000.00	deposit	50000.00	0.00	150000.00	unpaid	expired	t	2026-10-04 20:19:08	\N	\N	\N	tes booking sama	\N	\N	2026-10-04 20:19:08	\N	2026-10-04 21:20:00	f	\N	\N	20000.00	10000.00	0.00	\N	\N	\N	\N	\N
f9dccbc1-158c-4e6e-81ff-22df36b10f6d	MXB-20261004-WB1S	3287ebba-cce2-4675-a630-560433567874	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-14 21:22:00	2026-10-15 21:22:00	Jln Jl. Raya Krian, Bakalan, Katrungan, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur 61262, Indonesia	ChIJufejU5cJeC4RP2qCLGGD_Rw	-7.4211279	112.5846041	1.76	180000.00	0.00	0.00	180000.00	deposit	50000.00	0.00	130000.00	unpaid	pending_payment	t	2026-10-04 21:23:57	\N	\N	\N	tes kesamaan 2	\N	\N	2026-10-04 21:23:57	\N	2026-10-04 21:23:57	f	\N	\N	10000.00	0.00	0.00	\N	\N	\N	\N	\N
16a6c0ab-8f27-4851-b939-8b7b8c444333	MXB-20261004-FQVB	426c958e-2bc1-43d0-a3fb-a3c07042b880	82652bf1-5e26-4f49-8853-1a68dd9181de	b3e382bc-2ad3-4ef5-84d2-524ea7819121	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-05 17:08:00	2026-10-06 17:08:00	Tanggul, Kec. Wonoayu, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJCaz_i4oJeC4R7Lrs3_6FXvI	-7.4336705	112.5930470	3.52	180000.00	20000.00	0.00	200000.00	deposit	50000.00	200000.00	0.00	paid	on_delivery	t	2026-10-04 10:09:11	2026-10-04 10:13:00	\N	\N	tes dp satu	\N	\N	2026-10-04 10:09:11	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-04 10:13:04	f	9ae657a0-d924-4c64-95ba-849d6eafc4bb	personal	20000.00	0.00	20000.00	2026-10-04 10:13:00	\N	\N	\N	\N
ed888dbb-da39-4aba-9447-502715b8f821	MXB-20261004-NS2E	652008b7-7030-4265-889a-792238107601	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-11 17:17:00	2026-10-12 17:17:00	Tanggul, Kec. Wonoayu, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJCaz_i4oJeC4R7Lrs3_6FXvI	-7.4336705	112.5930470	3.52	180000.00	20000.00	0.00	200000.00	deposit	50000.00	0.00	150000.00	unpaid	expired	t	2026-10-04 10:17:46	\N	\N	\N	tes jangan bayar	\N	\N	2026-10-04 10:17:46	\N	2026-10-04 17:22:00	f	\N	\N	20000.00	10000.00	0.00	\N	\N	\N	\N	\N
9f7ef566-a953-4204-b736-c92e6d19dcc2	MXB-20261004-ELEU	e4a45994-442b-4bb9-83e9-0e0b1e9bf319	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-05 17:00:00	2026-10-06 17:00:00	Sadenganmijen, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJuXc-5o4JeC4Roknt0NaMn1k	-7.4277543	112.5855974	2.85	180000.00	20000.00	0.00	200000.00	deposit	50000.00	50000.00	150000.00	partial	on_delivery	t	2026-10-04 10:00:53	2026-10-04 20:12:08	\N	\N	testing dp	\N	\N	2026-10-04 10:00:53	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-04 20:12:08	f	9ae657a0-d924-4c64-95ba-849d6eafc4bb	company	20000.00	10000.00	10000.00	2026-10-04 20:12:08	\N	\N	\N	\N
0031ea50-efb2-45f1-a8bb-f72a64917d35	MXB-20261004-T13F	3287ebba-cce2-4675-a630-560433567874	82652bf1-5e26-4f49-8853-1a68dd9181de	b3e382bc-2ad3-4ef5-84d2-524ea7819121	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-14 21:22:00	2026-10-15 21:22:00	Jln Jl. Raya Krian, Bakalan, Katrungan, Kec. Krian, Kabupaten Sidoarjo, Jawa Timur 61262, Indonesia	ChIJufejU5cJeC4RP2qCLGGD_Rw	-7.4211279	112.5846041	1.76	180000.00	0.00	0.00	180000.00	deposit	50000.00	0.00	130000.00	unpaid	pending_payment	t	2026-10-04 21:24:00	\N	\N	\N	tes kesamaan 2	\N	\N	2026-10-04 21:24:00	\N	2026-10-04 21:24:00	f	\N	\N	10000.00	0.00	0.00	\N	\N	\N	\N	\N
08f5eba5-779a-4a91-a856-22531355f0d0	MXB-20261004-L3ZT	5195cd8d-914e-43ea-928e-055bb4d0af45	82652bf1-5e26-4f49-8853-1a68dd9181de	57f8b7d9-d5cd-4f75-b55f-22d0663ed416	24359e0f-adb4-4efc-bc75-eb139123c876	2026-10-15 21:27:00	2026-10-16 21:27:00	Tanggul, Kec. Wonoayu, Kabupaten Sidoarjo, Jawa Timur, Indonesia	ChIJCaz_i4oJeC4R7Lrs3_6FXvI	-7.4336705	112.5930470	3.52	180000.00	20000.00	0.00	200000.00	deposit	50000.00	0.00	150000.00	unpaid	pending_payment	t	2026-10-04 21:33:43	\N	\N	\N	tes kesamaan 3	\N	\N	2026-10-04 21:33:43	\N	2026-10-04 21:33:43	f	\N	\N	20000.00	10000.00	0.00	\N	\N	\N	\N	\N
\.


--
-- Data for Name: t_booking_idempotency; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.t_booking_idempotency (token, payment_code, booking_id, created_at, updated_at) FROM stdin;
7e438cd9-8781-4b26-8d49-73ebfaa6fc66	PAY-UKE8OX7D4M-1791123840	0031ea50-efb2-45f1-a8bb-f72a64917d35	2026-10-04 21:24:00	2026-10-04 21:24:00
1a53521c-b02d-4f8e-a85b-8a4a78e77c02	PAY-EC1TFGTBKM-1791124422	08f5eba5-779a-4a91-a856-22531355f0d0	2026-10-04 21:33:42	2026-10-04 21:33:43
\.


--
-- Data for Name: t_payment; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.t_payment (id, booking_id, payment_code, payment_type, payment_method, provider, external_payment_id, external_reference_id, requested_amount, paid_amount, status, qr_string, qr_url, expires_at, paid_at, created_by, created_at, updated_by, updated_at, is_deleted) FROM stdin;
f5a43e48-eaf6-46ac-8492-362d9a29747b	fc3c9f22-76e0-4bad-bcc2-0b931a1a392c	PAY-MUMIOBEVGP-1790669416	initial	qris	midtrans	3714ac10-4f68-4fcd-b189-d56bb47697cf	3714ac10-4f68-4fcd-b189-d56bb47697cf	220000.00	220000.00	succeeded	1919f77a-1291-4318-bbf9-6d89ef701c25	https://app.sandbox.midtrans.com/snap/v4/redirection/1919f77a-1291-4318-bbf9-6d89ef701c25	2026-09-29 09:10:16	2026-09-29 08:11:09	\N	2026-09-29 08:10:16	\N	2026-09-29 08:11:09	f
e6ea7fb0-a79c-4d34-9042-4021479e0240	920a16f3-58b3-4757-9dc5-3077cb20dd91	PAY-BG1EID8HAW-1790669942	initial	qris	midtrans	8f3142a3-80ef-4dc9-97c2-03b64c0b057f	8f3142a3-80ef-4dc9-97c2-03b64c0b057f	215000.00	215000.00	succeeded	eabf9897-ba0e-4b14-a438-82983e498127	https://app.sandbox.midtrans.com/snap/v4/redirection/eabf9897-ba0e-4b14-a438-82983e498127	2026-09-29 09:19:02	2026-09-29 08:19:20	\N	2026-09-29 08:19:02	\N	2026-09-29 08:19:20	f
21211681-a16e-418c-b494-950fe1898e03	b6f485fd-816b-4b4e-bd6d-66cfa1c4c9e4	PAY-0ZTA4EY3II-1790671124	initial	qris	midtrans	493399be-6234-44c5-a9fc-48c489c3630a	493399be-6234-44c5-a9fc-48c489c3630a	205000.00	205000.00	succeeded	8bde407e-af9c-4623-a712-88fcd03c6421	https://app.sandbox.midtrans.com/snap/v4/redirection/8bde407e-af9c-4623-a712-88fcd03c6421	2026-09-29 09:38:45	2026-09-29 08:39:01	\N	2026-09-29 08:38:45	\N	2026-09-29 08:39:01	f
8f4ab36a-0c28-4243-b11b-4d5d29bbd250	269f5953-015b-42e7-a33e-0959c0761931	PAY-OUE9OTFDQ9-1790949457	initial	qris	midtrans	49b7b5c9-e979-4dfc-b53f-0ac58e24d17e	49b7b5c9-e979-4dfc-b53f-0ac58e24d17e	200000.00	200000.00	succeeded	0c1712c5-a583-414b-b17c-f19ac669e0fd	https://app.sandbox.midtrans.com/snap/v4/redirection/0c1712c5-a583-414b-b17c-f19ac669e0fd	2026-10-02 14:57:37	2026-10-02 13:59:29	\N	2026-10-02 13:57:37	\N	2026-10-02 13:59:29	f
8929d868-5e14-4122-bf46-c1ccd5a79e05	22442282-ec53-4354-b7f3-b000fe4beab8	PAY-UHQ16GSCI1-1790949987	initial	qris	midtrans	61d04844-54c6-46aa-a097-e8e15ac32f29	61d04844-54c6-46aa-a097-e8e15ac32f29	200000.00	200000.00	succeeded	507ed5c6-534e-48f3-bb59-13a3c24f49f7	https://app.sandbox.midtrans.com/snap/v4/redirection/507ed5c6-534e-48f3-bb59-13a3c24f49f7	2026-10-02 15:06:27	2026-10-02 14:07:04	\N	2026-10-02 14:06:27	\N	2026-10-02 14:07:04	f
7f2c4400-c7c7-4323-ab24-7478871eaec9	453e5e77-6e62-4569-a5d5-f395ec943e98	PAY-EVMIM8UQJZ-1791106291	initial	qris	midtrans	155fb75f-5a72-4510-969d-bb3d3809d97c	155fb75f-5a72-4510-969d-bb3d3809d97c	200000.00	200000.00	succeeded	a0a3f750-83b8-4a12-894d-6ee48393d6bf	https://app.sandbox.midtrans.com/snap/v4/redirection/a0a3f750-83b8-4a12-894d-6ee48393d6bf	2026-10-04 10:31:31	2026-10-04 09:32:17	\N	2026-10-04 09:31:31	\N	2026-10-04 09:32:17	f
d18c706a-02c5-4d1b-a684-0c4d134fb4ec	51e560d5-44ef-42ab-8991-13e8b6ad5006	PAY-S0QELDXG5O-1791106422	initial	qris	midtrans	ca832226-1aa5-48d0-b7a8-a0234af35ca0	ca832226-1aa5-48d0-b7a8-a0234af35ca0	50000.00	50000.00	succeeded	97170fd5-cb7d-46b8-b3bb-fe019a9c4637	https://app.sandbox.midtrans.com/snap/v4/redirection/97170fd5-cb7d-46b8-b3bb-fe019a9c4637	2026-10-04 10:33:48	2026-10-04 09:34:05	\N	2026-10-04 09:33:48	\N	2026-10-04 09:34:05	f
c86441b0-c073-4eca-8e98-992aaa42cd8d	51e560d5-44ef-42ab-8991-13e8b6ad5006	REM-NKXZBZVGDX-1791106890	remaining	qris	midtrans	fa34252a-80c8-4f23-9888-9908670fc6af	fa34252a-80c8-4f23-9888-9908670fc6af	150000.00	150000.00	succeeded	d48507d4-b838-4fd2-bca4-463167c37671	https://app.sandbox.midtrans.com/snap/v4/redirection/d48507d4-b838-4fd2-bca4-463167c37671	2026-10-04 10:41:30	2026-10-04 09:41:47	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-04 09:41:30	\N	2026-10-04 09:41:47	f
5a55708c-4f88-40f7-9850-b2fc2c3ea1d7	6cec40b2-a8c9-45ac-82b0-eefc14112789	PAY-331PE0JALN-1791107559	initial	qris	midtrans	55e5b927-4d53-4fee-9611-6af2c8beb7a6	55e5b927-4d53-4fee-9611-6af2c8beb7a6	50000.00	50000.00	succeeded	c07b47b1-79cd-4dfa-95a2-4d0d43b2b35d	https://app.sandbox.midtrans.com/snap/v4/redirection/c07b47b1-79cd-4dfa-95a2-4d0d43b2b35d	2026-10-04 10:52:41	2026-10-04 09:57:00	\N	2026-10-04 09:52:41	\N	2026-10-04 09:57:00	f
15641d72-9ce4-4319-b2e6-41d81af81bd3	6cec40b2-a8c9-45ac-82b0-eefc14112789	CSH-P572F4QEBC-1791107969	remaining	cash	manual	\N	\N	150000.00	150000.00	succeeded	\N	\N	2026-10-04 09:59:29	2026-10-04 09:59:29	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-04 09:59:29	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-04 09:59:29	f
e377c098-e76d-4196-b18f-2e926a0d8fdd	9f7ef566-a953-4204-b736-c92e6d19dcc2	PAY-Z6SDMOFP6L-1791108053	initial	qris	midtrans	c361ce9a-3582-4800-bcb6-b664c2e329b2	c361ce9a-3582-4800-bcb6-b664c2e329b2	50000.00	50000.00	succeeded	97b40b43-722c-4849-a1c9-3ccf52d05587	https://app.sandbox.midtrans.com/snap/v4/redirection/97b40b43-722c-4849-a1c9-3ccf52d05587	2026-10-04 11:00:53	2026-10-04 10:01:54	\N	2026-10-04 10:00:53	\N	2026-10-04 10:01:54	f
a9cb4b79-829d-4a3d-b499-92e29200bc0a	16a6c0ab-8f27-4851-b939-8b7b8c444333	PAY-RLDIZVZJWO-1791108551	initial	qris	midtrans	b44f9a75-4df2-45b3-a9dc-37bbff13da1e	b44f9a75-4df2-45b3-a9dc-37bbff13da1e	50000.00	50000.00	succeeded	9bc7d8f7-097e-401a-bb80-583a0055aa60	https://app.sandbox.midtrans.com/snap/v4/redirection/9bc7d8f7-097e-401a-bb80-583a0055aa60	2026-10-04 11:09:11	2026-10-04 10:11:09	\N	2026-10-04 10:09:11	\N	2026-10-04 10:11:09	f
0c49ebbc-f1a8-4448-846b-86e5a7b1346c	16a6c0ab-8f27-4851-b939-8b7b8c444333	CSH-GDRBDYW5ZZ-1791108784	remaining	cash	manual	\N	\N	150000.00	150000.00	succeeded	\N	\N	2026-10-04 10:13:04	2026-10-04 10:13:04	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-04 10:13:04	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-04 10:13:04	f
720b9a67-027f-456f-84d1-c97e00484c96	ed888dbb-da39-4aba-9447-502715b8f821	PAY-S3GDGASVMD-1791109066	initial	qris	midtrans	\N	\N	50000.00	0.00	expired	94f29c15-c848-493d-85c9-700831d49e52	https://app.sandbox.midtrans.com/snap/v4/redirection/94f29c15-c848-493d-85c9-700831d49e52	2026-10-04 11:17:46	\N	\N	2026-10-04 10:17:46	\N	2026-10-04 17:22:00	f
37513983-5bc2-438f-99ee-698db9b60d92	9f7ef566-a953-4204-b736-c92e6d19dcc2	REM-W4UYVUCH7L-1791119546	remaining	qris	midtrans	\N	\N	150000.00	0.00	expired	85bcbe61-8bf2-4d06-baba-e091c5e4b926	https://app.sandbox.midtrans.com/snap/v4/redirection/85bcbe61-8bf2-4d06-baba-e091c5e4b926	2026-10-04 21:12:27	\N	9ae657a0-d924-4c64-95ba-849d6eafc4bb	2026-10-04 20:12:27	\N	2026-10-04 21:13:28	f
60ecdeed-d27f-4972-96a1-90ada025de15	94738909-2388-45de-b517-e47d56f05451	PAY-MY3B0WAOXN-1791119922	initial	qris	midtrans	\N	\N	50000.00	0.00	expired	8e851252-c173-4e4a-b5ea-b57b950fba00	https://app.sandbox.midtrans.com/snap/v4/redirection/8e851252-c173-4e4a-b5ea-b57b950fba00	2026-10-04 21:19:03	\N	\N	2026-10-04 20:19:03	\N	2026-10-04 21:20:00	f
48c55d04-a291-43aa-8ff7-c55e630588d0	69a386f5-8483-4f5c-a4f5-0a035a9a5c60	PAY-TQBID2X5PN-1791119948	initial	qris	midtrans	\N	\N	50000.00	0.00	expired	93f0231d-2d65-4f9f-a6ee-cf90bfa41987	https://app.sandbox.midtrans.com/snap/v4/redirection/93f0231d-2d65-4f9f-a6ee-cf90bfa41987	2026-10-04 21:19:28	\N	\N	2026-10-04 20:19:28	\N	2026-10-04 21:20:00	f
2630496e-9ab8-4695-a66d-7ad199fcdd35	f9dccbc1-158c-4e6e-81ff-22df36b10f6d	PAY-OCKKMQYQZ3-1791123837	initial	qris	midtrans	\N	\N	50000.00	0.00	pending	691b291f-5941-46fc-962f-f6ae201d85a8	https://app.sandbox.midtrans.com/snap/v4/redirection/691b291f-5941-46fc-962f-f6ae201d85a8	2026-10-04 22:23:57	\N	\N	2026-10-04 21:23:57	\N	2026-10-04 21:23:57	f
ced4f447-965f-4836-b438-c5ee1f081ad0	0031ea50-efb2-45f1-a8bb-f72a64917d35	PAY-KYTCQNLCL3-1791123840	initial	qris	midtrans	\N	\N	50000.00	0.00	pending	5936d9f9-2f93-4073-9c9c-7e495b1a50b3	https://app.sandbox.midtrans.com/snap/v4/redirection/5936d9f9-2f93-4073-9c9c-7e495b1a50b3	2026-10-04 22:24:00	\N	\N	2026-10-04 21:24:00	\N	2026-10-04 21:24:00	f
775ceaf8-b84f-4606-ad4d-454b3cb76e52	08f5eba5-779a-4a91-a856-22531355f0d0	PAY-ZRYI1IULOC-1791124423	initial	qris	midtrans	\N	\N	50000.00	0.00	pending	ecfd1d71-b320-427e-9dfd-7c5632004a95	https://app.sandbox.midtrans.com/snap/v4/redirection/ecfd1d71-b320-427e-9dfd-7c5632004a95	2026-10-04 22:33:43	\N	\N	2026-10-04 21:33:43	\N	2026-10-04 21:33:43	f
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) FROM stdin;
\.


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 4, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 1, false);


--
-- Name: auth_role auth_role_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auth_role
    ADD CONSTRAINT auth_role_name_key UNIQUE (name);


--
-- Name: auth_role auth_role_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auth_role
    ADD CONSTRAINT auth_role_pkey PRIMARY KEY (id);


--
-- Name: auth_user auth_user_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auth_user
    ADD CONSTRAINT auth_user_email_unique UNIQUE (email);


--
-- Name: auth_user auth_user_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auth_user
    ADD CONSTRAINT auth_user_pkey PRIMARY KEY (id);


--
-- Name: auth_user auth_user_username_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auth_user
    ADD CONSTRAINT auth_user_username_unique UNIQUE (username);


--
-- Name: t_booking booking_code_uk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_booking
    ADD CONSTRAINT booking_code_uk UNIQUE (booking_code);


--
-- Name: t_booking booking_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_booking
    ADD CONSTRAINT booking_pk PRIMARY KEY (id);


--
-- Name: c_business_setting business_setting_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_business_setting
    ADD CONSTRAINT business_setting_pk PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: c_customer customer_phone_number_uk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_customer
    ADD CONSTRAINT customer_phone_number_uk UNIQUE (phone_number);


--
-- Name: c_customer customer_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_customer
    ADD CONSTRAINT customer_pk PRIMARY KEY (id);


--
-- Name: c_delivery_rate delivery_rate_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_delivery_rate
    ADD CONSTRAINT delivery_rate_pk PRIMARY KEY (id);


--
-- Name: t_payment external_payment_id_uk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_payment
    ADD CONSTRAINT external_payment_id_uk UNIQUE (external_payment_id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: c_faq faq_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_faq
    ADD CONSTRAINT faq_pk PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: c_menu menu_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_menu
    ADD CONSTRAINT menu_pk PRIMARY KEY (id);


--
-- Name: menu_role menu_role_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.menu_role
    ADD CONSTRAINT menu_role_pk PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: t_payment payment_code_uk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_payment
    ADD CONSTRAINT payment_code_uk UNIQUE (payment_code);


--
-- Name: t_payment payment_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_payment
    ADD CONSTRAINT payment_pk PRIMARY KEY (id);


--
-- Name: c_playstation_unit playstation_unit_code_uk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_playstation_unit
    ADD CONSTRAINT playstation_unit_code_uk UNIQUE (unit_code);


--
-- Name: c_playstation_unit playstation_unit_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_playstation_unit
    ADD CONSTRAINT playstation_unit_pk PRIMARY KEY (id);


--
-- Name: c_rental_package rental_package_code_uk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_rental_package
    ADD CONSTRAINT rental_package_code_uk UNIQUE (code);


--
-- Name: c_rental_package rental_package_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_rental_package
    ADD CONSTRAINT rental_package_pk PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: t_booking_idempotency t_booking_idempotency_booking_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_booking_idempotency
    ADD CONSTRAINT t_booking_idempotency_booking_id_unique UNIQUE (booking_id);


--
-- Name: t_booking_idempotency t_booking_idempotency_payment_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_booking_idempotency
    ADD CONSTRAINT t_booking_idempotency_payment_code_unique UNIQUE (payment_code);


--
-- Name: t_booking_idempotency t_booking_idempotency_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_booking_idempotency
    ADD CONSTRAINT t_booking_idempotency_pkey PRIMARY KEY (token);


--
-- Name: c_terms_condition terms_condition_pk; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.c_terms_condition
    ADD CONSTRAINT terms_condition_pk PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: failed_jobs_connection_queue_failed_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX failed_jobs_connection_queue_failed_at_index ON public.failed_jobs USING btree (connection, queue, failed_at);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: auth_user auth_user_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auth_user
    ADD CONSTRAINT auth_user_role_id_fkey FOREIGN KEY (role_id) REFERENCES public.auth_role(id);


--
-- Name: t_booking booking_customer_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_booking
    ADD CONSTRAINT booking_customer_fk FOREIGN KEY (customer_id) REFERENCES public.c_customer(id) ON DELETE RESTRICT;


--
-- Name: t_booking booking_playstation_unit_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_booking
    ADD CONSTRAINT booking_playstation_unit_fk FOREIGN KEY (playstation_unit_id) REFERENCES public.c_playstation_unit(id) ON DELETE RESTRICT;


--
-- Name: t_booking booking_rental_package_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_booking
    ADD CONSTRAINT booking_rental_package_fk FOREIGN KEY (rental_package_id) REFERENCES public.c_rental_package(id) ON DELETE RESTRICT;


--
-- Name: t_booking booking_terms_condition_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_booking
    ADD CONSTRAINT booking_terms_condition_fk FOREIGN KEY (terms_condition_id) REFERENCES public.c_terms_condition(id) ON DELETE RESTRICT;


--
-- Name: t_booking fk_booking_driver; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_booking
    ADD CONSTRAINT fk_booking_driver FOREIGN KEY (driver_id) REFERENCES public.auth_user(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: menu_role menu_role_menu_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.menu_role
    ADD CONSTRAINT menu_role_menu_id_fkey FOREIGN KEY (menu_id) REFERENCES public.c_menu(id);


--
-- Name: menu_role menu_role_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.menu_role
    ADD CONSTRAINT menu_role_role_id_fkey FOREIGN KEY (role_id) REFERENCES public.auth_role(id);


--
-- Name: t_payment payment_booking_fk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_payment
    ADD CONSTRAINT payment_booking_fk FOREIGN KEY (booking_id) REFERENCES public.t_booking(id) ON DELETE RESTRICT;


--
-- Name: t_booking_idempotency t_booking_idempotency_booking_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.t_booking_idempotency
    ADD CONSTRAINT t_booking_idempotency_booking_id_foreign FOREIGN KEY (booking_id) REFERENCES public.t_booking(id) ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--

