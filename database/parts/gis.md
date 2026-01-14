## GIS (Geographic Information Systems)

Instead of saving locations as plain numbers (lat/lng) and doing math in your app, GIS lets MySQL/Postgres understand geography natively:

- “Which shops are within 2 km of this point?”
- “Does this location fall inside a delivery zone?”
- “Which roads intersect this area?”


We will Explain it for Postgres, however mysql do almost the same concepts...

### Intro
GIS in PostgreSQL is provided by an extension called PostGIS. PostGIS turns PostgreSQL into a full geospatial database, not just “lat/lng storage”.

With PostGIS, PostgreSQL can:
- Understand Earth geometry
- Store real geographic shapes
- Perform accurate spatial math
- Use spatial indexes efficiently

### Enable Postgres GIS

`CREATE EXTENSION IF NOT EXISTS postgis;`

That single line adds:
- 1000+ spatial functions
- Spatial types
- Spatial indexes
- Coordinate systems

Tip: enabling extention is done only once and all spatial will be avilable through your postgres installation


### Geometry vs Geography (very important)

PostGIS has two spatial worlds:

- geometry (flat / projected)
    - Uses planar math
    - Fast
    - Requires map projection (UTM, Web Mercator, etc.)
    - Used for cities, zones, engineering
    - If SRID is 4326 (WGS84) → coordinates are `degrees`

- geography (Earth-aware 🌍)
    - Uses spheroid math
    - Accurate distances on Earth
    - Slightly slower
    - Used for GPS, real-world distances
    - SRID is almost always 4326 → coordinates are `meters`

```sql
-- create geometry point with SRID = 0 which means unknown SRID
SELECT ST_MakePoint(31.2357, 30.0444) AS geo_point;

-- create geometry point with SRID=4326
SELECT ST_SetSRID(ST_MakePoint(?,?), 4326))

-- create geography by default geography SRID is 4326
SELECT ST_MakePoint(?, ?)::geography AS geo_point;

-- distance here is in degrees not meters, because geometry SRID 4326 is degrees
-- you can't get distance_meters from distance_degrees in equation or something
SELECT ST_Distance(
    ST_SetSRID(ST_MakePoint(31.2357, 30.0444), 4326), -- geometry a
    ST_SetSRID(ST_MakePoint(31.25, 30.05), 4326) -- geometry b
) AS distance_degrees;


-- transform to projected SRID to get meters
SELECT ST_Distance(
    ST_Transform(ST_SetSRID(ST_MakePoint(31.2357, 30.0444), 4326), 32636), --geometry is in SRID=32636 projected SRID (meters)
    ST_Transform(ST_SetSRID(ST_MakePoint(31.25, 30.05), 4326), 32636)
) AS distance_meters;

-- get distance in meters since geography is in meters
SELECT ST_Distance(
    ST_MakePoint(31.2357, 30.0444)::geography,
    ST_MakePoint(31.25, 30.05)::geography
) AS distance_meters;
```

Tip: for realworld applications always use `Geography` to store data

Tip: for realworld applications always cast to geometry when you are doing containment checks such as ST_Contains, ST_Intersects



### Spatial data types

PostGIS follows OpenGIS and supports:

| Type                 | Meaning      | Example                          | Realworld          |
| -------------------- | ------------ |----------------------            |------------------- |
| `POINT`              | Location     |POINT(31.2357 30.0444) -- lon lat | GPS location       |
| `LINESTRING`         | Path         |LINESTRING(31.2300 30.0400, 31.2357 30.0444, 31.2400 30.0500) --lon lat | Road, Route, pipe line|
| `POLYGON`            | Area         |POLYGON((31.2300 30.0400,31.2400 30.0400,31.2400 30.0500,31.2300 30.0500,31.2300 30.0400))| City, Delivery Zone, Land Plot, GEO-FENCING |
| `MULTI*`             | Collections  |MULTIPOINT((31.2357 30.0444),(31.2500 30.0600),(31.2200 30.0300)) | Branch locations, ATMS |

<br>

Tip: order of co-ordinates by OpenGIS is always longitude,latitude.. however in daily life is always latitude,longitude .. latitude is around equator while longitude is around north,south pole


### Spatial functions
- Create spatial objects.
    - ST_MakePoint(x, y [, z [, m]])
    - ST_MakeLine(geom1, geom2 | geom[])
    - ST_MakePolygon(linestring)
    - ST_GeomFromText(wkt, srid)
    - ST_GeogFromText(wkt)
    - ST_FromEWKT(ewkt)
    - ST_FromGeoJSON(json)
    - ST_Collect(geom[])
    - ST_Point(x, y)

- Check how geometries relate to each other (boolean return true/false)
    - ST_Intersects(a, b) .. geometry only
    - ST_Contains(a, b) .. geometry only
    - ST_Within(a, b) .. geometry only
    - ST_Touches(a, b)
    - ST_Overlaps(a, b)
    - ST_Crosses(a, b)
    - ST_Disjoint(a, b)
    - ST_Equals(a, b)
    - ST_DWithin(a, b, distance) .. geography and geometry

- measure distance
    - ST_Distance(a, b) .. geometry and geography


Tip: a,b must be same type of geometry or geography ❌ Invalid: `ST_Distance(geom, geog)`

Tip: a,b must be same SRID

Tip: to improve performance you would need to add index on the column `CREATE INDEX idx_geom ON table USING GIST (geom);`


### Show restaurants within 3 km of the customer, given customer/resturants are both POINT

```sql
    -- create table with location being geography to be safer with meters with SRID=4326 for GPS Location
    CREATE TABLE restaurants (
        id          BIGSERIAL PRIMARY KEY,
        name        TEXT NOT NULL,
        location    geography(Point, 4326) NOT NULL
    );

    -- create spatial index for quick queries
    CREATE INDEX restaurants_location_gix ON restaurants USING GIST (location);

    -- insert data into resurants
    INSERT INTO restaurants (name, location) VALUES ('Koshary El Tahrir', ST_MakePoint(31.2357, 30.0444)::geography);

    -- find resturants within 3k meters diamters
    SELECT * FROM restaurants r WHERE ST_DWithin( r.location, ST_MakePoint(:c_lon, :c_lat)::geography, 3000);
```

### Which rider can pick this order fastest?

```sql
-- create table
CREATE TABLE couriers (
    courier_id   BIGSERIAL PRIMARY KEY,
    name         TEXT NOT NULL,
    status       TEXT NOT NULL DEFAULT 'available',  -- available, busy, offline
    location     geography(Point, 4326) NOT NULL
);

-- create index
CREATE INDEX couriers_location_gix ON couriers USING GIST(location);

-- insert data
INSERT INTO couriers (name, status, location) VALUES
('Ahmed', 'available', ST_MakePoint(31.2357, 30.0444)::geography),
('Mona', 'busy', ST_MakePoint(31.2400, 30.0500)::geography),
('Omar', 'available', ST_MakePoint(31.2300, 30.0400)::geography);


-- find courier
SELECT courier_id FROM couriers WHERE status = 'available' ORDER BY ST_Distance(location, ST_MakePoint(:lon, :lat)::geography) LIMIT 1;
```


### Charge based on actual travel distance, not straight line


```sql
-- Restaurants table
CREATE TABLE restaurants (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    location GEOGRAPHY(POINT, 4326)  -- use GEOGRAPHY to get meters directly
);

-- Customers table
CREATE TABLE customers (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    location GEOGRAPHY(POINT, 4326)
);

-- Insert a restaurant
INSERT INTO restaurants (name, location) VALUES ('Pizza Place', ST_MakePoint(31.2357, 30.0444)::GEOGRAPHY);

-- Insert a customer
INSERT INTO customers (name, location) VALUES ('John Doe', ST_MakePoint(31.2450, 30.0500)::GEOGRAPHY);

-- find length of line
SELECT ST_Length(ST_MakeLine(
        (SELECT location FROM restaurants WHERE id = 1),
        (SELECT location FROM customers WHERE id = 1)
    ));
```

### 


```sql
-- create table
CREATE TABLE delivery_zones (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    geom GEOGRAPHY(POLYGON, 4326)  -- each zone is a polygon
);

-- create index
CREATE INDEX delivery_zones_geom_gix ON delivery_zones USING GIST(geom);

-- insert data
INSERT INTO delivery_zones (name, geom)
VALUES (
    'helwan',
    ST_MakePolygon(
        ST_MakeLine(ARRAY[
            ST_MakePoint(31.2300, 30.0400),
            ST_MakePoint(31.2400, 30.0400),
            ST_MakePoint(31.2400, 30.0500),
            ST_MakePoint(31.2300, 30.0500),
            ST_MakePoint(31.2300, 30.0400)  -- must close polygon
        ])
    )::GEOGRAPHY
);

-- insert data
INSERT INTO delivery_zones (name, geom)
VALUES (
    'Downtown',
    ST_GeogFromText('POLYGON((
        31.2300 30.0400,
        31.2400 30.0400,
        31.2400 30.0500,
        31.2300 30.0500,
        31.2300 30.0400
    ))')
);

--insert data 
INSERT INTO delivery_zones (name, geom)
VALUES (
    'Uptown',
    ST_GeomFromGeoJSON('{
        "type": "Polygon",
        "coordinates": [[
            [31.2400, 30.0500],
            [31.2500, 30.0500],
            [31.2500, 30.0600],
            [31.2400, 30.0600],
            [31.2400, 30.0500]
        ]]
    }')::geography
);



-- find delivery zone for customer location 
-- this is using contains which works only with geometry.. so this is checking the locations ignoring spherical shape of earth
SELECT name FROM delivery_zones WHERE ST_Contains(delivery_zones.geom::geometry, ST_SetSRID(ST_MakePoint(31.2450, 30.0500),4326)) limit 1;


-- invalid because contains doesnt work with geography
SELECT name FROM delivery_zones WHERE ST_Contains(zone.geom, ST_MakePoint(31.2450, 30.0500)::GEOGRAPHY) limit 1;

-- invalid because its comparing SRID=4326 with point of SRID=0
SELECT name FROM delivery_zones WHERE ST_Contains(delivery_zones.geom::geometry, ST_MakePoint(31.2450, 30.0500)) limit 1;

```
