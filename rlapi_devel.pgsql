--
-- PostgreSQL database dump
--

-- Dumped from database version 10.8 (Ubuntu 10.8-0ubuntu0.18.04.1)
-- Dumped by pg_dump version 10.8 (Ubuntu 10.8-0ubuntu0.18.04.1)

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
-- Name: banned_domains_short; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.banned_domains_short (
    id uuid,
    domain text
);


ALTER TABLE public.banned_domains_short OWNER TO postgres;

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
-- Name: domains; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.domains (
    id uuid,
    domain_name text,
    official boolean,
    wildcard boolean,
    public boolean,
    verified boolean,
    verification_hash text,
    user_id uuid,
    api_key uuid,
    bucket text
);


ALTER TABLE public.domains OWNER TO postgres;

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
    sha1 text,
    deleted boolean
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
-- Name: promo_codes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.promo_codes (
    id uuid,
    code text,
    uses integer,
    max_uses integer,
    promo_tier text,
    expired boolean
);


ALTER TABLE public.promo_codes OWNER TO postgres;

--
-- Name: shortened_urls; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.shortened_urls (
    user_id uuid,
    token uuid,
    id uuid,
    short_name text,
    url text,
    url_safe boolean,
    "timestamp" integer
);


ALTER TABLE public.shortened_urls OWNER TO postgres;

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
-- Data for Name: banned_domains_short; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.banned_domains_short (id, domain) FROM stdin;
\.


--
-- Data for Name: buckets; Type: TABLE DATA; Schema: public; Owner: rlapi_devel
--

COPY public.buckets (user_id, bucket_name, allocated_domain) FROM stdin;
\.


--
-- Data for Name: domains; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.domains (id, domain_name, official, wildcard, public, verified, verification_hash, user_id, api_key, bucket) FROM stdin;
\.


--
-- Data for Name: files; Type: TABLE DATA; Schema: public; Owner: rlapi_devel
--

COPY public.files (filename, originalfilename, "timestamp", user_id, token, md5, sha1, deleted) FROM stdin;
\.


--
-- Data for Name: password_resets; Type: TABLE DATA; Schema: public; Owner: rlapi_devel
--

COPY public.password_resets (id, email, expiry_date, used) FROM stdin;
\.


--
-- Data for Name: promo_codes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.promo_codes (id, code, uses, max_uses, promo_tier, expired) FROM stdin;
\.


--
-- Data for Name: shortened_urls; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.shortened_urls (user_id, token, id, short_name, url, url_safe, "timestamp") FROM stdin;
\.


--
-- Data for Name: tiers; Type: TABLE DATA; Schema: public; Owner: rlapi_devel
--

COPY public.tiers (tier, maximum_filesize, api_keys, private_domains, users_per_bucket, bucket_limit) FROM stdin;
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
-- Name: TABLE banned_domains_short; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.banned_domains_short TO rlapi_devel;


--
-- Name: TABLE domains; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.domains TO rlapi_devel;


--
-- Name: TABLE promo_codes; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.promo_codes TO rlapi_devel;


--
-- Name: TABLE shortened_urls; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.shortened_urls TO rlapi_devel;


--
-- PostgreSQL database dump complete
--
