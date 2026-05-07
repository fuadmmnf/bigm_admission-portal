from pathlib import Path
import re
import urllib.request

BASE = Path(__file__).resolve().parents[1]
DATA_DIR = BASE / 'database' / 'seeders' / 'data'
DATA_DIR.mkdir(parents=True, exist_ok=True)

PROGRAMS = [
    {
        'name': 'Human Resource Management',
        'code': 'HRM',
        'legacy_name': 'HRM',
        'source': 'google-form-19th-batch-mpa',
    },
    {
        'name': 'Governance and Public Policy',
        'code': 'GPP',
        'legacy_name': 'GPP',
        'source': 'google-form-19th-batch-mpa',
    },
    {
        'name': 'International Economic Relations',
        'code': 'IER',
        'legacy_name': 'IER',
        'source': 'google-form-19th-batch-mpa',
    },
    {
        'name': 'Project Management',
        'code': 'PM',
        'legacy_name': 'PM',
        'source': 'google-form-19th-batch-mpa',
    },
    {
        'name': 'Procurement and Supply Chain Management',
        'code': 'PSCM',
        'legacy_name': 'PSCM',
        'source': 'google-form-19th-batch-mpa',
    },
    {
        'name': 'Public Private Financial Management',
        'code': 'PPFM',
        'legacy_name': 'PPFM',
        'source': 'google-form-19th-batch-mpa',
    },
]

DIVISION_NAME_MAP = {
    'Chattagram': ('Chattogram', 'Chittagong'),
    'Barisal': ('Barishal', 'Barisal'),
}

DISTRICT_NAME_MAP = {
    'Comilla': ('Cumilla', 'Comilla'),
    'Coxsbazar': ("Cox's Bazar", "Cox's Bazar"),
    'Jessore': ('Jashore', 'Jessore'),
    'Bogra': ('Bogura', 'Bogra'),
    'Jhalakathi': ('Jhalokati', 'Jhalokati'),
    'Moulvibazar': ('Moulvibazar', 'Maulvibazar'),
    'Barisal': ('Barishal', 'Barisal'),
    'Chittagong': ('Chattogram', 'Chittagong'),
    'Nawabganj': ('Chapainawabganj', 'Nawabganj'),
    'Chapainawabganj': ('Chapainawabganj', 'Nawabganj'),
}

UPAZILA_NAME_MAP = {
    'Comilla Sadar': ('Cumilla Sadar', 'Comilla Sadar'),
    'Comilla Sadar Dakshin': ('Cumilla Sadar Dakshin', 'Comilla Sadar Dakshin'),
    'Jessore Sadar': ('Jashore Sadar', 'Jessore Sadar'),
    'Barisal Sadar': ('Barishal Sadar', 'Barisal Sadar'),
    'Chittagong Sadar': ('Chattogram Sadar', 'Chittagong Sadar'),
}


def fetch(path: str) -> str:
    url = f'https://raw.githubusercontent.com/devfaysal/laravel-bangladesh-geocode/2.8/{path}'
    with urllib.request.urlopen(url) as response:
        return response.read().decode('utf-8')


def parse_entries(text: str) -> list[dict]:
    rows = []

    for line in text.splitlines():
        line = line.strip()

        if not line.startswith('array('):
            continue

        pairs = dict(re.findall(r"'([^']+)'\s*=>\s*(NULL|'(?:[^']|'')*')", line))

        cleaned = {}
        for key, value in pairs.items():
            if value == 'NULL':
                cleaned[key] = None
            else:
                cleaned[key] = value[1:-1].replace("''", "'").strip()

        if cleaned:
            rows.append(cleaned)

    return rows


def php_export(value, indent: int = 0) -> str:
    spacing = ' ' * indent

    if isinstance(value, dict):
        rows = [f"{spacing}    {php_export(key)} => {php_export(item, indent + 4)}" for key, item in value.items()]
        return "[\n" + ",\n".join(rows) + f"\n{spacing}]"

    if isinstance(value, list):
        rows = [f"{spacing}    {php_export(item, indent + 4)}" for item in value]
        return "[\n" + ",\n".join(rows) + f"\n{spacing}]"

    if value is None:
        return 'null'

    if isinstance(value, bool):
        return 'true' if value else 'false'

    if isinstance(value, (int, float)):
        return repr(value)

    escaped = str(value).replace('\\', '\\\\').replace("'", "\\'")
    return f"'{escaped}'"


def write_php_array(filename: str, data: list[dict]) -> None:
    target = DATA_DIR / filename
    content = "<?php\n\nreturn " + php_export(data) + ";\n"
    target.write_text(content, encoding='utf-8')


def main() -> None:
    divisions = parse_entries(fetch('src/Seeders/DivisionSeeder.php'))
    districts = parse_entries(fetch('src/Seeders/DistrictSeeder.php'))
    upazilas = parse_entries(fetch('src/Seeders/UpazilaSeeder.php'))

    normalized_divisions = []
    for row in divisions:
        name, legacy_name = DIVISION_NAME_MAP.get(row['name'], (row['name'], None))
        normalized_divisions.append({
            'source_id': int(row['id']),
            'name': name,
            'bn_name': row.get('bn_name'),
            'legacy_name': legacy_name,
            'url': row.get('url'),
        })

    normalized_districts = []
    for row in districts:
        name, legacy_name = DISTRICT_NAME_MAP.get(row['name'], (row['name'], None))
        normalized_districts.append({
            'source_id': int(row['id']),
            'division_source_id': int(row['division_id']),
            'name': name,
            'bn_name': row.get('bn_name'),
            'legacy_name': legacy_name,
            'latitude': float(row['lat']) if row.get('lat') else None,
            'longitude': float(row['lon']) if row.get('lon') else None,
            'url': row.get('url'),
        })

    normalized_upazilas = []
    for row in upazilas:
        name, legacy_name = UPAZILA_NAME_MAP.get(row['name'], (row['name'], None))
        normalized_upazilas.append({
            'source_id': int(row['id']),
            'district_source_id': int(row['district_id']),
            'name': name,
            'bn_name': row.get('bn_name'),
            'legacy_name': legacy_name,
            'url': row.get('url'),
        })

    write_php_array('programs.php', PROGRAMS)
    write_php_array('bangladesh_divisions.php', normalized_divisions)
    write_php_array('bangladesh_districts.php', normalized_districts)
    write_php_array('bangladesh_upazilas.php', normalized_upazilas)

    print(
        'Generated:',
        len(PROGRAMS),
        'programs,',
        len(normalized_divisions),
        'divisions,',
        len(normalized_districts),
        'districts,',
        len(normalized_upazilas),
        'upazilas',
    )


if __name__ == '__main__':
    main()

