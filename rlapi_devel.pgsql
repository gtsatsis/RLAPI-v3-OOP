--
-- PostgreSQL database dump
--

-- Dumped from database version 10.6 (Ubuntu 10.6-0ubuntu0.18.04.1)
-- Dumped by pg_dump version 10.6 (Ubuntu 10.6-0ubuntu0.18.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

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
-- Name: addons; Type: TABLE; Schema: public; Owner: rlapi_devel
--

CREATE TABLE public.addons (
    user_id uuid,
    whitelabel_enabled boolean
);


ALTER TABLE public.addons OWNER TO rlapi_devel;

--
-- Name: buckets; Type: TABLE; Schema: public; Owner: rlapi_devel
--

CREATE TABLE public.buckets (
    user_id uuid,
    bucket_name text,
    allocated_domain text
);


ALTER TABLE public.buckets OWNER TO rlapi_devel;

--
-- Name: files; Type: TABLE; Schema: public; Owner: rlapi_devel
--

CREATE TABLE public.files (
    filename text,
    originalfilename text,
    "timestamp" text,
    user_id uuid,
    token uuid,
    md5 text,
    sha1 text
);


ALTER TABLE public.files OWNER TO rlapi_devel;

--
-- Name: password_resets; Type: TABLE; Schema: public; Owner: rlapi_devel
--

CREATE TABLE public.password_resets (
    id uuid,
    email text,
    expiry_date text,
    used boolean
);


ALTER TABLE public.password_resets OWNER TO rlapi_devel;

--
-- Name: tiers; Type: TABLE; Schema: public; Owner: rlapi_devel
--

CREATE TABLE public.tiers (
    tier text,
    maximum_filesize bigint,
    api_keys integer,
    private_domains integer,
    users_per_bucket integer,
    bucket_limit integer
);


ALTER TABLE public.tiers OWNER TO rlapi_devel;

--
-- Name: tokens; Type: TABLE; Schema: public; Owner: rlapi_devel
--

CREATE TABLE public.tokens (
    user_id uuid,
    token uuid,
    name text
);


ALTER TABLE public.tokens OWNER TO rlapi_devel;

--
-- Name: users; Type: TABLE; Schema: public; Owner: rlapi_devel
--

CREATE TABLE public.users (
    id uuid,
    username text,
    password text,
    email text,
    tier text,
    is_admin boolean,
    is_blocked boolean,
    fh_enabled boolean,
    ads_enabled boolean,
    verified boolean
);


ALTER TABLE public.users OWNER TO rlapi_devel;

--
-- Name: verification_emails; Type: TABLE; Schema: public; Owner: rlapi_devel
--

CREATE TABLE public.verification_emails (
    user_id uuid,
    verification_id uuid,
    email text,
    used boolean
);


ALTER TABLE public.verification_emails OWNER TO rlapi_devel;

--
-- Data for Name: addons; Type: TABLE DATA; Schema: public; Owner: rlapi_devel
--

COPY public.addons (user_id, whitelabel_enabled) FROM stdin;
\.


--
-- Data for Name: buckets; Type: TABLE DATA; Schema: public; Owner: rlapi_devel
--

COPY public.buckets (user_id, bucket_name, allocated_domain) FROM stdin;
\.


--
-- Data for Name: files; Type: TABLE DATA; Schema: public; Owner: rlapi_devel
--

COPY public.files (filename, originalfilename, "timestamp", user_id, token, md5, sha1) FROM stdin;
\.


--
-- Data for Name: password_resets; Type: TABLE DATA; Schema: public; Owner: rlapi_devel
--

COPY public.password_resets (id, email, expiry_date, used) FROM stdin;
\.


--
-- Data for Name: tiers; Type: TABLE DATA; Schema: public; Owner: rlapi_devel
--

COPY public.tiers (tier, maximum_filesize, api_keys, private_domains, users_per_bucket, bucket_limit) FROM stdin;
free	1048576	3	0	0	0
premium	262144000	10	2	8	2
deluxe	524288000	999	999	16	16
\.


--
-- Data for Name: tokens; Type: TABLE DATA; Schema: public; Owner: rlapi_devel
--

COPY public.tokens (user_id, token, name) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: rlapi_devel
--

COPY public.users (id, username, password, email, tier, is_admin, is_blocked, fh_enabled, ads_enabled, verified) FROM stdin;
\.


--
-- Data for Name: verification_emails; Type: TABLE DATA; Schema: public; Owner: rlapi_devel
--

COPY public.verification_emails (user_id, verification_id, email, used) FROM stdin;
\.


--
-- PostgreSQL database dump complete
--

