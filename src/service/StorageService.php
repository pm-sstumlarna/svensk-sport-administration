<?php

namespace SSA\service;

use PDO;
use SSA\library\StorageServiceInterface;

class StorageService implements StorageServiceInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function startTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commitTransaction(): bool
    {
        return $this->pdo->commit();
    }

    public function rollbackTransaction(): bool
    {
        return $this->pdo->rollBack();
    }

    public function check(): bool
    {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (\PDOException) {
            return false;
        }
    }

    public function initialize(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS syllabuses (
                id CHAR(36) PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                goals TEXT,
                prerequisites TEXT,
                createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                lastUpdate DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS courses (
                id CHAR(36) PRIMARY KEY,
                syllabusId CHAR(36) NOT NULL,
                teacher VARCHAR(255) NOT NULL,
                speed VARCHAR(50),
                lectureCount INTEGER,
                detailedPlan TEXT,
                createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (syllabusId) REFERENCES syllabuses(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS natural_persons (
                id CHAR(36) PRIMARY KEY,
                firstName VARCHAR(255) NOT NULL,
                lastName VARCHAR(255) NOT NULL,
                personalIdentityNumber VARCHAR(20),
                createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS person_passed_courses (
                naturalPersonId CHAR(36) NOT NULL,
                courseId CHAR(36) NOT NULL,
                passedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (naturalPersonId, courseId),
                FOREIGN KEY (naturalPersonId) REFERENCES natural_persons(id) ON DELETE CASCADE,
                FOREIGN KEY (courseId) REFERENCES courses(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS emails (
                id CHAR(36) PRIMARY KEY,
                naturalPersonId CHAR(36),
                organizationId CHAR(36),
                email VARCHAR(255) NOT NULL,
                isPrimary BOOLEAN DEFAULT 0,
                FOREIGN KEY (naturalPersonId) REFERENCES natural_persons(id) ON DELETE CASCADE,
                FOREIGN KEY (organizationId) REFERENCES organizations(id) ON DELETE CASCADE,
                CHECK ((naturalPersonId IS NOT NULL AND organizationId IS NULL) OR (naturalPersonId IS NULL AND organizationId IS NOT NULL))
            );

            CREATE TABLE IF NOT EXISTS phones (
                id CHAR(36) PRIMARY KEY,
                naturalPersonId CHAR(36),
                organizationId CHAR(36),
                phoneNumber VARCHAR(50) NOT NULL,
                isPrimary BOOLEAN DEFAULT 0,
                FOREIGN KEY (naturalPersonId) REFERENCES natural_persons(id) ON DELETE CASCADE,
                FOREIGN KEY (organizationId) REFERENCES organizations(id) ON DELETE CASCADE,
                CHECK ((naturalPersonId IS NOT NULL AND organizationId IS NULL) OR (naturalPersonId IS NULL AND organizationId IS NOT NULL))
            );

            CREATE TABLE IF NOT EXISTS addresses (
                id CHAR(36) PRIMARY KEY,
                naturalPersonId CHAR(36),
                organizationId CHAR(36),
                street VARCHAR(255),
                zipCode VARCHAR(20),
                city VARCHAR(100),
                country VARCHAR(100),
                isPrimary BOOLEAN DEFAULT 0,
                FOREIGN KEY (naturalPersonId) REFERENCES natural_persons(id) ON DELETE CASCADE,
                FOREIGN KEY (organizationId) REFERENCES organizations(id) ON DELETE CASCADE,
                CHECK ((naturalPersonId IS NOT NULL AND organizationId IS NULL) OR (naturalPersonId IS NULL AND organizationId IS NOT NULL))
            );

            CREATE TABLE IF NOT EXISTS person_roles (
                id CHAR(36) PRIMARY KEY,
                naturalPersonId CHAR(36) NOT NULL,
                role VARCHAR(50) NOT NULL,
                FOREIGN KEY (naturalPersonId) REFERENCES natural_persons(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS organizations (
                id CHAR(36) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                orgNumber VARCHAR(50),
                address TEXT,
                website VARCHAR(255),
                createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS organization_board_members (
                organizationId CHAR(36) NOT NULL,
                naturalPersonId CHAR(36) NOT NULL,
                role VARCHAR(100) NOT NULL,
                PRIMARY KEY (organizationId, naturalPersonId),
                FOREIGN KEY (organizationId) REFERENCES organizations(id) ON DELETE CASCADE,
                FOREIGN KEY (naturalPersonId) REFERENCES natural_persons(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS groups (
                id CHAR(36) PRIMARY KEY,
                organizationId CHAR(36),
                name VARCHAR(255) NOT NULL,
                description TEXT,
                FOREIGN KEY (organizationId) REFERENCES organizations(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS memberships (
                id CHAR(36) PRIMARY KEY,
                naturalPersonId CHAR(36) NOT NULL,
                organizationId CHAR(36) NOT NULL,
                groupId CHAR(36),
                status VARCHAR(50),
                type VARCHAR(50),
                createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (naturalPersonId) REFERENCES natural_persons(id) ON DELETE CASCADE,
                FOREIGN KEY (organizationId) REFERENCES organizations(id) ON DELETE CASCADE,
                FOREIGN KEY (groupId) REFERENCES groups(id) ON DELETE SET NULL
            );

            CREATE TABLE IF NOT EXISTS guardian_relations (
                id CHAR(36) PRIMARY KEY,
                childId CHAR(36) NOT NULL,
                guardianId CHAR(36) NOT NULL,
                relationType VARCHAR(50),
                FOREIGN KEY (childId) REFERENCES natural_persons(id) ON DELETE CASCADE,
                FOREIGN KEY (guardianId) REFERENCES natural_persons(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS news (
                id CHAR(36) PRIMARY KEY,
                organizationId CHAR(36) NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                author VARCHAR(255),
                publishDate DATETIME,
                createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (organizationId) REFERENCES organizations(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS activities (
                id CHAR(36) PRIMARY KEY,
                groupId CHAR(36),
                title VARCHAR(255) NOT NULL,
                description TEXT,
                startTime DATETIME NOT NULL,
                endTime DATETIME NOT NULL,
                location VARCHAR(255),
                activityType VARCHAR(50)
            );

            CREATE TABLE IF NOT EXISTS invitations (
                activityId CHAR(36) NOT NULL,
                naturalPersonId CHAR(36) NOT NULL,
                status VARCHAR(50),
                PRIMARY KEY (activityId, naturalPersonId),
                FOREIGN KEY (activityId) REFERENCES activities(id) ON DELETE CASCADE,
                FOREIGN KEY (naturalPersonId) REFERENCES natural_persons(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS attendance (
                activityId CHAR(36) NOT NULL,
                naturalPersonId CHAR(36) NOT NULL,
                status VARCHAR(50) NOT NULL,
                comment TEXT,
                PRIMARY KEY (activityId, naturalPersonId),
                FOREIGN KEY (activityId) REFERENCES activities(id) ON DELETE CASCADE,
                FOREIGN KEY (naturalPersonId) REFERENCES natural_persons(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS goals (
                id CHAR(36) PRIMARY KEY,
                naturalPersonId CHAR(36) NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                targetValue VARCHAR(255),
                actualValue VARCHAR(255),
                status VARCHAR(50),
                FOREIGN KEY (naturalPersonId) REFERENCES natural_persons(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS fee_levels (
                id CHAR(36) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                amount DECIMAL(10, 2) NOT NULL
            );

            CREATE TABLE IF NOT EXISTS messages (
                id CHAR(36) PRIMARY KEY,
                subject VARCHAR(255),
                body TEXT,
                createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS invoices (
                id CHAR(36) PRIMARY KEY,
                membershipId CHAR(36),
                amount DECIMAL(10, 2),
                due_date DATE,
                status VARCHAR(50),
                reference_number VARCHAR(255)
            );

            CREATE TABLE IF NOT EXISTS bookings (
                id CHAR(36) PRIMARY KEY,
                naturalPersonId CHAR(36) NOT NULL,
                courseId CHAR(36) NOT NULL,
                guardianName VARCHAR(255),
                guardianEmail VARCHAR(255),
                guardianPhone VARCHAR(50),
                status VARCHAR(50) DEFAULT 'Pending',
                createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (naturalPersonId) REFERENCES natural_persons(id) ON DELETE CASCADE,
                FOREIGN KEY (courseId) REFERENCES courses(id) ON DELETE CASCADE
            );
        ";
        $this->pdo->exec($sql);
    }

    public function migrate(): void
    {
        // Placeholder for future migrations
        $this->initialize();
    }

    public function seedExampleData(): void
    {
        $orgId = '00000000-0000-0000-0000-000000000000';
        if (!$this->getOrganization($orgId)) {
            $this->createOrganization([
                'id' => $orgId,
                'name' => 'Svenska Sportklubben',
                'orgNumber' => '123456-7890',
                'emails' => [
                    ['email' => 'info@svenskasportklubben.se', 'isPrimary' => 1],
                    ['email' => 'support@svenskasportklubben.se', 'isPrimary' => 0]
                ],
                'phones' => [
                    ['phoneNumber' => '08-123 45 67', 'isPrimary' => 1],
                    ['phoneNumber' => '08-765 43 21', 'isPrimary' => 0]
                ],
                'address' => 'Idrottsvägen 1, 123 45 Stockholm',
                'website' => 'https://www.svenskasportklubben.se'
            ]);
        }

        $natural_persons = [
            [
                'id' => '00000000-0000-0000-0000-000000000001',
                'firstName' => 'Johan',
                'lastName' => 'Andersson',
                'emails' => [
                    ['email' => 'johan.andersson@example.com', 'isPrimary' => 1],
                    ['email' => 'johan.work@example.com', 'isPrimary' => 0]
                ],
                'phones' => [
                    ['phoneNumber' => '070-1234567', 'isPrimary' => 1],
                    ['phoneNumber' => '08-7654321', 'isPrimary' => 0]
                ],
                'addresses' => [
                    [
                        'street' => 'Storgatan 1',
                        'zipCode' => '12345',
                        'city' => 'Stockholm',
                        'country' => 'Sweden',
                        'isPrimary' => 1
                    ]
                ],
                'personalIdentityNumber' => '19800101-1234',
                'roles' => ['Teacher']
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000002',
                'firstName' => 'Maria',
                'lastName' => 'Svensson',
                'emails' => [
                    ['email' => 'maria.svensson@example.com', 'isPrimary' => 1]
                ],
                'phones' => [
                    ['phoneNumber' => '073-9876543', 'isPrimary' => 1]
                ],
                'addresses' => [
                    [
                        'street' => 'Lillvägen 2',
                        'zipCode' => '54321',
                        'city' => 'Göteborg',
                        'country' => 'Sweden',
                        'isPrimary' => 1
                    ]
                ],
                'personalIdentityNumber' => '19850505-5678',
                'roles' => ['Member', 'Guardian']
            ]
        ];

        foreach ($natural_persons as $person) {
            if (!$this->getNaturalPerson($person['id'])) {
                $this->createNaturalPerson($person);
            }
        }

        $activities = [
            [
                'id' => '10000000-0000-0000-0000-000000000001',
                'title' => 'Football Training',
                'startTime' => '2026-02-01 18:00:00',
                'endTime' => '2026-02-01 19:30:00',
                'location' => 'Field A',
                'activityType' => 'Träning'
            ],
            [
                'id' => '10000000-0000-0000-0000-000000000002',
                'title' => 'Team Meeting',
                'startTime' => '2026-02-05 19:00:00',
                'endTime' => '2026-02-05 20:00:00',
                'location' => 'Club House',
                'activityType' => 'Möte'
            ]
        ];

        foreach ($activities as $activity) {
            if (!$this->getActivity($activity['id'])) {
                $this->createActivity($activity);
            }
        }

        $syllabuses = [
            [
                'id' => '20000000-0000-0000-0000-000000000001',
                'title' => 'Baddaren',
                'description' => 'Första nivån i simskolan där barn bygger upp vattenvana och trygghet i vattnet.',
                'goals' => 'Vattenvana, doppa huvudet, bubbla, glida, flyta med stöd, hoppa från kanten. Lek och övningar för att våga doppa, blåsa bubblor, flyta och glida.',
                'prerequisites' => 'Inga – barnet ska kunna vistas i vatten tillsammans med ledare.'
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000002',
                'title' => 'Bläckfisken',
                'description' => 'Andra nivån för barn med god vattenvana som inte har problem med att doppa hela huvudet eller få stänk i ansiktet.',
                'goals' => 'Flyta på rygg och mage, glida i strömlinje, ta sig fram med benspark med hjälpmedel. Fortsatt vattenvana, strömlinje, benspark rygg/mage, rotationer.',
                'prerequisites' => 'Våga doppa hela huvudet och kunna bubbla samt hoppa från kanten.'
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000003',
                'title' => 'Pingvinen',
                'description' => 'Tredje nivån där simförmågan tar steget mot högre nivå. Barnen ska ha god kontroll och trygghet i vattnet.',
                'goals' => '25 m simning i magläge och 25 m i ryggläge, grundläggande andning i crawl/bröst. Ben- och armtag i rygg- och magläge, grunder i crawl och bröstsim, flyt/rotation.',
                'prerequisites' => 'Kunna flyta/rotera samt glida i strömlinje på mage och rygg.'
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000004',
                'title' => 'Fisken',
                'description' => 'Fjärde nivån på djupt vatten. Barnen fortsätter utveckla vattenvana, vattenkänsla, trygghet och säkerhet samt ökad simförmåga.',
                'goals' => '100 m sammanhängande simning varav minst 25 m crawl och 25 m rygg, kunna dyka. Teknik i crawl/rygg, introduktion till bröstsim och fjärilskick, vändningar och starter på grundnivå.',
                'prerequisites' => 'Klara Pingvinen Guld eller motsvarande (ca 25 m i mag- och ryggläge).'
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000005',
                'title' => 'Hajen',
                'description' => 'Femte och avslutande nivån i simskolan där simteknik och uthållighet tränas i alla fyra simsätten.',
                'goals' => '200 m medley-inspirerad simning (minst 50 m crawl, 50 m rygg, 50 m bröst), grundläggande fjäril. Förbättrad teknik i crawl, rygg och bröst, introduktion/utveckling av fjäril, starter, vändningar och simidrottsvana.',
                'prerequisites' => 'Klara Fisken Guld eller motsvarande.'
            ]
        ];

        foreach ($syllabuses as $syllabus) {
            if (!$this->getSyllabus($syllabus['id'])) {
                $this->createSyllabus($syllabus);
            }
        }

        $courses = [
            [
                'id' => '30000000-0000-0000-0000-000000000001',
                'syllabusId' => '20000000-0000-0000-0000-000000000001', // Baddaren
                'teacher' => 'Anna Johansson',
                'speed' => 'normal speed',
                'lectureCount' => 10,
                'detailedPlan' => [
                    ['lectureNumber' => 1, 'content' => 'Vattenvana och lek.'],
                    ['lectureNumber' => 2, 'content' => 'Bubbla och doppa.'],
                    ['lectureNumber' => 3, 'content' => 'Glida som en pil.'],
                    ['lectureNumber' => 4, 'content' => 'Flyta som en sjöstjärna.'],
                    ['lectureNumber' => 5, 'content' => 'Hoppa från kanten.'],
                    ['lectureNumber' => 6, 'content' => 'Sparka med benen.'],
                    ['lectureNumber' => 7, 'content' => 'Röra på armarna.'],
                    ['lectureNumber' => 8, 'content' => 'Sammansatt simning.'],
                    ['lectureNumber' => 9, 'content' => 'Repetition.'],
                    ['lectureNumber' => 10, 'content' => 'Avslutning och märken.']
                ]
            ],
            [
                'id' => '30000000-0000-0000-0000-000000000002',
                'syllabusId' => '20000000-0000-0000-0000-000000000002', // Bläckfisken
                'teacher' => 'Stefan Larsson',
                'speed' => 'normal speed',
                'lectureCount' => 10,
                'detailedPlan' => [
                    ['lectureNumber' => 1, 'content' => 'Repetition av vattenvana.'],
                    ['lectureNumber' => 2, 'content' => 'Flyta på mage utan hjälp.'],
                    ['lectureNumber' => 3, 'content' => 'Flyta på rygg.'],
                    ['lectureNumber' => 4, 'content' => 'Benspark på mage.'],
                    ['lectureNumber' => 5, 'content' => 'Benspark på rygg.'],
                    ['lectureNumber' => 6, 'content' => 'Glida i strömlinje.'],
                    ['lectureNumber' => 7, 'content' => 'Rotation mage till rygg.'],
                    ['lectureNumber' => 8, 'content' => 'Hoppa på djupt vatten.'],
                    ['lectureNumber' => 9, 'content' => 'Simma 5 meter.'],
                    ['lectureNumber' => 10, 'content' => 'Avslutning.']
                ]
            ],
            [
                'id' => '30000000-0000-0000-0000-000000000003',
                'syllabusId' => '20000000-0000-0000-0000-000000000003', // Pingvinen
                'teacher' => 'Maria Bergman',
                'speed' => 'normal speed',
                'lectureCount' => 10,
                'detailedPlan' => [
                    ['lectureNumber' => 1, 'content' => 'Simning 10 meter.'],
                    ['lectureNumber' => 2, 'content' => 'Crawl-armtag.'],
                    ['lectureNumber' => 3, 'content' => 'Rygg-simning teknik.'],
                    ['lectureNumber' => 4, 'content' => 'Andning i crawl.'],
                    ['lectureNumber' => 5, 'content' => 'Flyta 1 minut.'],
                    ['lectureNumber' => 6, 'content' => 'Hoppa och dyka.'],
                    ['lectureNumber' => 7, 'content' => 'Bröstsim armtag.'],
                    ['lectureNumber' => 8, 'content' => 'Bröstsim bentag.'],
                    ['lectureNumber' => 9, 'content' => 'Simma 25 meter.'],
                    ['lectureNumber' => 10, 'content' => 'Märkesprov.']
                ]
            ],
            [
                'id' => '30000000-0000-0000-0000-000000000004',
                'syllabusId' => '20000000-0000-0000-0000-000000000004', // Fisken
                'teacher' => 'Erik Nilsson',
                'speed' => 'normal speed',
                'lectureCount' => 10,
                'detailedPlan' => [
                    ['lectureNumber' => 1, 'content' => 'Uthållighet 50 meter.'],
                    ['lectureNumber' => 2, 'content' => 'Crawlteknik förfining.'],
                    ['lectureNumber' => 3, 'content' => 'Ryggsim teknik.'],
                    ['lectureNumber' => 4, 'content' => 'Bröstsim sammansatt.'],
                    ['lectureNumber' => 5, 'content' => 'Dyka från pall.'],
                    ['lectureNumber' => 6, 'content' => 'Vändningar.'],
                    ['lectureNumber' => 7, 'content' => 'Längddykning.'],
                    ['lectureNumber' => 8, 'content' => 'Livräddning grunder.'],
                    ['lectureNumber' => 9, 'content' => 'Simma 100 meter.'],
                    ['lectureNumber' => 10, 'content' => 'Avslutning.']
                ]
            ],
            [
                'id' => '30000000-0000-0000-0000-000000000005',
                'syllabusId' => '20000000-0000-0000-0000-000000000005', // Hajen
                'teacher' => 'Linda Holm',
                'speed' => 'normal speed',
                'lectureCount' => 10,
                'detailedPlan' => [
                    ['lectureNumber' => 1, 'content' => 'Uthållighet 100 meter.'],
                    ['lectureNumber' => 2, 'content' => 'Medleyteknik.'],
                    ['lectureNumber' => 3, 'content' => 'Fjärilsim grunder.'],
                    ['lectureNumber' => 4, 'content' => 'Snabbhetsträning.'],
                    ['lectureNumber' => 5, 'content' => 'Tävlingsstarter.'],
                    ['lectureNumber' => 6, 'content' => 'Vändningar alla simsätt.'],
                    ['lectureNumber' => 7, 'content' => 'Vattenprov.'],
                    ['lectureNumber' => 8, 'content' => 'Simidrottskunskap.'],
                    ['lectureNumber' => 9, 'content' => 'Simma 200 meter.'],
                    ['lectureNumber' => 10, 'content' => 'Hajen Guld prov.']
                ]
            ]
        ];

        foreach ($courses as $course) {
            if (!$this->getCourse($course['id'])) {
                $this->createCourse($course);
            }
        }
    }

    public function createNaturalPerson(array $data): string
    {
        $id = $data['id'] ?? bin2hex(random_bytes(18)); // Simple fallback if UUID not provided
        $stmt = $this->pdo->prepare("INSERT INTO natural_persons (id, firstName, lastName, personalIdentityNumber) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $id,
            $data['firstName'],
            $data['lastName'],
            $data['personalIdentityNumber'] ?? null
        ]);

        $this->updatePersonDetails($id, $data);

        return $id;
    }

    public function getNaturalPerson(string $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM natural_persons WHERE id = ?");
        $stmt->execute([$id]);
        $person = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$person) {
            return null;
        }

        // Emails
        $stmt = $this->pdo->prepare("SELECT email, isPrimary FROM emails WHERE naturalPersonId = ?");
        $stmt->execute([$id]);
        $person['emails'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Phones
        $stmt = $this->pdo->prepare("SELECT phoneNumber, isPrimary FROM phones WHERE naturalPersonId = ?");
        $stmt->execute([$id]);
        $person['phones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Addresses
        $stmt = $this->pdo->prepare("SELECT street, zipCode, city, country, isPrimary FROM addresses WHERE naturalPersonId = ?");
        $stmt->execute([$id]);
        $person['addresses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Roles
        $stmt = $this->pdo->prepare("SELECT role FROM person_roles WHERE naturalPersonId = ?");
        $stmt->execute([$id]);
        $person['roles'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Passed Courses
        $stmt = $this->pdo->prepare("SELECT courseId, passedAt FROM person_passed_courses WHERE naturalPersonId = ?");
        $stmt->execute([$id]);
        $person['passedCourses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $person;
    }

    public function updateNaturalPerson(string $id, array $data): bool
    {
        $fields = [];
        $params = [];
        
        $allowedFields = ['firstName', 'lastName', 'personalIdentityNumber'];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $params[] = $id;
            $sql = "UPDATE natural_persons SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        }

        $this->updatePersonDetails($id, $data);

        return true;
    }

    private function updatePersonDetails(string $id, array $data): void
    {
        // Emails
        if (isset($data['emails']) && is_array($data['emails'])) {
            $this->pdo->prepare("DELETE FROM emails WHERE naturalPersonId = ?")->execute([$id]);
            foreach ($data['emails'] as $emailData) {
                $emailId = bin2hex(random_bytes(18));
                $email = is_array($emailData) ? $emailData['email'] : $emailData;
                $isPrimary = is_array($emailData) ? ($emailData['isPrimary'] ?? 0) : 0;
                $stmt = $this->pdo->prepare("INSERT INTO emails (id, naturalPersonId, email, isPrimary) VALUES (?, ?, ?, ?)");
                $stmt->execute([$emailId, $id, $email, $isPrimary]);
            }
        } elseif (isset($data['email'])) {
            $this->pdo->prepare("DELETE FROM emails WHERE naturalPersonId = ?")->execute([$id]);
            $emailId = bin2hex(random_bytes(18));
            $stmt = $this->pdo->prepare("INSERT INTO emails (id, naturalPersonId, email, isPrimary) VALUES (?, ?, ?, ?)");
            $stmt->execute([$emailId, $id, $data['email'], 1]);
        }

        // Phones
        if (isset($data['phones']) && is_array($data['phones'])) {
            $this->pdo->prepare("DELETE FROM phones WHERE naturalPersonId = ?")->execute([$id]);
            foreach ($data['phones'] as $phoneData) {
                $phoneId = bin2hex(random_bytes(18));
                $phone = is_array($phoneData) ? $phoneData['phoneNumber'] : $phoneData;
                $isPrimary = is_array($phoneData) ? ($phoneData['isPrimary'] ?? 0) : 0;
                $stmt = $this->pdo->prepare("INSERT INTO phones (id, naturalPersonId, phoneNumber, isPrimary) VALUES (?, ?, ?, ?)");
                $stmt->execute([$phoneId, $id, $phone, $isPrimary]);
            }
        } elseif (isset($data['phoneNumber'])) {
            $this->pdo->prepare("DELETE FROM phones WHERE naturalPersonId = ?")->execute([$id]);
            $phoneId = bin2hex(random_bytes(18));
            $stmt = $this->pdo->prepare("INSERT INTO phones (id, naturalPersonId, phoneNumber, isPrimary) VALUES (?, ?, ?, ?)");
            $stmt->execute([$phoneId, $id, $data['phoneNumber'], 1]);
        }

        // Addresses
        if (isset($data['addresses']) && is_array($data['addresses'])) {
            $this->pdo->prepare("DELETE FROM addresses WHERE naturalPersonId = ?")->execute([$id]);
            foreach ($data['addresses'] as $addressData) {
                $addressId = bin2hex(random_bytes(18));
                $isPrimary = $addressData['isPrimary'] ?? 0;
                $stmt = $this->pdo->prepare("INSERT INTO addresses (id, naturalPersonId, street, zipCode, city, country, isPrimary) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $addressId,
                    $id,
                    $addressData['street'] ?? null,
                    $addressData['zipCode'] ?? null,
                    $addressData['city'] ?? null,
                    $addressData['country'] ?? null,
                    $isPrimary
                ]);
            }
        } elseif (isset($data['address'])) {
            $this->pdo->prepare("DELETE FROM addresses WHERE naturalPersonId = ?")->execute([$id]);
            $addressData = $data['address'];
            $addressId = bin2hex(random_bytes(18));
            $stmt = $this->pdo->prepare("INSERT INTO addresses (id, naturalPersonId, street, zipCode, city, country, isPrimary) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $addressId,
                $id,
                $addressData['street'] ?? null,
                $addressData['zipCode'] ?? null,
                $addressData['city'] ?? null,
                $addressData['country'] ?? null,
                1
            ]);
        }

        // Roles
        if (isset($data['roles']) && is_array($data['roles'])) {
            $this->pdo->prepare("DELETE FROM person_roles WHERE naturalPersonId = ?")->execute([$id]);
            foreach ($data['roles'] as $role) {
                $roleId = bin2hex(random_bytes(18));
                $stmt = $this->pdo->prepare("INSERT INTO person_roles (id, naturalPersonId, role) VALUES (?, ?, ?)");
                $stmt->execute([$roleId, $id, $role]);
            }
        }

        // Passed Courses
        if (isset($data['passedCourses']) && is_array($data['passedCourses'])) {
            $this->pdo->prepare("DELETE FROM person_passed_courses WHERE naturalPersonId = ?")->execute([$id]);
            foreach ($data['passedCourses'] as $courseData) {
                $courseId = is_array($courseData) ? $courseData['courseId'] : $courseData;
                $passedAt = is_array($courseData) ? ($courseData['passedAt'] ?? date('Y-m-d H:i:s')) : date('Y-m-d H:i:s');
                $stmt = $this->pdo->prepare("INSERT INTO person_passed_courses (naturalPersonId, courseId, passedAt) VALUES (?, ?, ?)");
                $stmt->execute([$id, $courseId, $passedAt]);
            }
        }
    }

    public function listNaturalPersons(): array
    {
        $persons = $this->pdo->query("SELECT * FROM natural_persons")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($persons as &$person) {
            $id = $person['id'];

            // Emails
            $stmt = $this->pdo->prepare("SELECT email, isPrimary FROM emails WHERE naturalPersonId = ?");
            $stmt->execute([$id]);
            $person['emails'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Phones
            $stmt = $this->pdo->prepare("SELECT phoneNumber, isPrimary FROM phones WHERE naturalPersonId = ?");
            $stmt->execute([$id]);
            $person['phones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Addresses
            $stmt = $this->pdo->prepare("SELECT street, zipCode, city, country, isPrimary FROM addresses WHERE naturalPersonId = ?");
            $stmt->execute([$id]);
            $person['addresses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Roles
            $stmt = $this->pdo->prepare("SELECT role FROM person_roles WHERE naturalPersonId = ?");
            $stmt->execute([$id]);
            $person['roles'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return $persons;
    }

    public function createActivity(array $data): string
    {
        $id = $data['id'] ?? bin2hex(random_bytes(18));
        $stmt = $this->pdo->prepare("INSERT INTO activities (id, groupId, title, description, startTime, endTime, location, activityType) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $id,
            $data['groupId'] ?? null,
            $data['title'],
            $data['description'] ?? null,
            $data['startTime'],
            $data['endTime'],
            $data['location'] ?? null,
            $data['activityType'] ?? null
        ]);
        return $id;
    }

    public function getActivity(string $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM activities WHERE id = ?");
        $stmt->execute([$id]);
        $activity = $stmt->fetch(PDO::FETCH_ASSOC);
        return $activity ?: null;
    }

    public function updateActivity(string $id, array $data): bool
    {
        $fields = [];
        $params = [];
        
        $allowedFields = ['groupId', 'title', 'description', 'startTime', 'endTime', 'location', 'activityType'];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $params[] = $id;
            $sql = "UPDATE activities SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        }

        return true;
    }

    public function listActivities(): array
    {
        return $this->pdo->query("SELECT * FROM activities")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createInvitation(string $activityId, array $data): bool
    {
        $stmt = $this->pdo->prepare("INSERT OR REPLACE INTO invitations (activityId, naturalPersonId, status) VALUES (?, ?, ?)");
        return $stmt->execute([$activityId, $data['naturalPersonId'], $data['status'] ?? 'Invited']);
    }

    public function updateInvitation(string $activityId, string $naturalPersonId, array $data): bool
    {
        $stmt = $this->pdo->prepare("UPDATE invitations SET status = ? WHERE activityId = ? AND naturalPersonId = ?");
        return $stmt->execute([$data['status'], $activityId, $naturalPersonId]);
    }

    public function getAttendance(string $activityId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM attendance WHERE activityId = ?");
        $stmt->execute([$activityId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateAttendance(string $activityId, array $data): bool
    {
        foreach ($data as $attendance) {
            $stmt = $this->pdo->prepare("INSERT OR REPLACE INTO attendance (activityId, naturalPersonId, status, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$activityId, $attendance['naturalPersonId'], $attendance['status'], $attendance['comment'] ?? null]);
        }
        return true;
    }

    public function createGoal(string $naturalPersonId, array $data): string
    {
        $id = $data['id'] ?? bin2hex(random_bytes(18));
        $stmt = $this->pdo->prepare("INSERT INTO goals (id, naturalPersonId, title, description, targetValue, actualValue, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $id,
            $naturalPersonId,
            $data['title'],
            $data['description'] ?? null,
            $data['targetValue'] ?? null,
            $data['actualValue'] ?? null,
            $data['status'] ?? 'Pågående'
        ]);
        return $id;
    }

    public function listGoals(string $naturalPersonId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM goals WHERE naturalPersonId = ?");
        $stmt->execute([$naturalPersonId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listInvoices(): array
    {
        return $this->pdo->query("SELECT * FROM invoices")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createInvoices(): bool
    {
        // Real implementation would look at memberships and generate invoices
        return true;
    }

    public function getLokSupportReport(): array
    {
        // Simple aggregation of attendance
        return $this->pdo->query("SELECT activityId, count(*) as participantCount FROM attendance WHERE status = 'Närvarande' GROUP BY activityId")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listGroups(): array
    {
        return $this->pdo->query("SELECT * FROM groups")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createMessage(array $data): bool
    {
        $id = $data['id'] ?? bin2hex(random_bytes(18));
        $stmt = $this->pdo->prepare("INSERT INTO messages (id, subject, body) VALUES (?, ?, ?)");
        return $stmt->execute([
            $id,
            $data['subject'],
            $data['body']
        ]);
    }

    public function listFeeLevels(): array
    {
        return $this->pdo->query("SELECT * FROM fee_levels")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createFeeLevel(array $data): string
    {
        $id = $data['id'] ?? bin2hex(random_bytes(18));
        $stmt = $this->pdo->prepare("INSERT INTO fee_levels (id, name, amount) VALUES (?, ?, ?)");
        $stmt->execute([$id, $data['name'], $data['amount']]);
        return $id;
    }

    public function getMemberStatistics(): array
    {
        $totalMembers = $this->pdo->query("SELECT count(*) FROM natural_persons")->fetchColumn();
        return [
            'totalMembers' => (int)$totalMembers
        ];
    }

    public function listOrganizations(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM organizations");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createOrganization(array $data): string
    {
        $id = $data['id'] ?? bin2hex(random_bytes(18));
        $stmt = $this->pdo->prepare("INSERT INTO organizations (id, name, orgNumber, address, website) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $id,
            $data['name'],
            $data['orgNumber'] ?? null,
            $data['address'] ?? null,
            $data['website'] ?? null
        ]);

        $this->updateOrganizationDetails($id, $data);

        return $id;
    }

    public function getOrganization(string $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM organizations WHERE id = ?");
        $stmt->execute([$id]);
        $org = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$org) {
            return null;
        }

        // Emails
        $stmt = $this->pdo->prepare("SELECT email, isPrimary FROM emails WHERE organizationId = ?");
        $stmt->execute([$org['id']]);
        $org['emails'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Phones
        $stmt = $this->pdo->prepare("SELECT phoneNumber, isPrimary FROM phones WHERE organizationId = ?");
        $stmt->execute([$org['id']]);
        $org['phones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Addresses
        $stmt = $this->pdo->prepare("SELECT street, zipCode, city, country, isPrimary FROM addresses WHERE organizationId = ?");
        $stmt->execute([$org['id']]);
        $org['addresses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $org;
    }

    public function updateOrganization(string $id, array $data): bool
    {
        $fields = [];
        $params = [];
        
        $allowedFields = ['name', 'orgNumber', 'address', 'website'];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $params[] = $id;
            $sql = "UPDATE organizations SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        }

        $this->updateOrganizationDetails($id, $data);

        return true;
    }

    private function updateOrganizationDetails(string $id, array $data): void
    {
        // Emails
        if (isset($data['emails']) && is_array($data['emails'])) {
            $this->pdo->prepare("DELETE FROM emails WHERE organizationId = ?")->execute([$id]);
            foreach ($data['emails'] as $emailData) {
                $emailId = bin2hex(random_bytes(18));
                $email = is_array($emailData) ? $emailData['email'] : $emailData;
                $isPrimary = is_array($emailData) ? ($emailData['isPrimary'] ?? 0) : 0;
                $stmt = $this->pdo->prepare("INSERT INTO emails (id, organizationId, email, isPrimary) VALUES (?, ?, ?, ?)");
                $stmt->execute([$emailId, $id, $email, $isPrimary]);
            }
        } elseif (isset($data['email'])) {
            $this->pdo->prepare("DELETE FROM emails WHERE organizationId = ?")->execute([$id]);
            $emailId = bin2hex(random_bytes(18));
            $stmt = $this->pdo->prepare("INSERT INTO emails (id, organizationId, email, isPrimary) VALUES (?, ?, ?, ?)");
            $stmt->execute([$emailId, $id, $data['email'], 1]);
        }

        // Phones
        if (isset($data['phones']) && is_array($data['phones'])) {
            $this->pdo->prepare("DELETE FROM phones WHERE organizationId = ?")->execute([$id]);
            foreach ($data['phones'] as $phoneData) {
                $phoneId = bin2hex(random_bytes(18));
                $phone = is_array($phoneData) ? $phoneData['phoneNumber'] : $phoneData;
                $isPrimary = is_array($phoneData) ? ($phoneData['isPrimary'] ?? 0) : 0;
                $stmt = $this->pdo->prepare("INSERT INTO phones (id, organizationId, phoneNumber, isPrimary) VALUES (?, ?, ?, ?)");
                $stmt->execute([$phoneId, $id, $phone, $isPrimary]);
            }
        } elseif (isset($data['phoneNumber'])) {
            $this->pdo->prepare("DELETE FROM phones WHERE organizationId = ?")->execute([$id]);
            $phoneId = bin2hex(random_bytes(18));
            $stmt = $this->pdo->prepare("INSERT INTO phones (id, organizationId, phoneNumber, isPrimary) VALUES (?, ?, ?, ?)");
            $stmt->execute([$phoneId, $id, $data['phoneNumber'], 1]);
        }

        // Addresses
        if (isset($data['addresses']) && is_array($data['addresses'])) {
            $this->pdo->prepare("DELETE FROM addresses WHERE organizationId = ?")->execute([$id]);
            foreach ($data['addresses'] as $addressData) {
                $addressId = bin2hex(random_bytes(18));
                $isPrimary = $addressData['isPrimary'] ?? 0;
                $stmt = $this->pdo->prepare("INSERT INTO addresses (id, organizationId, street, zipCode, city, country, isPrimary) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $addressId,
                    $id,
                    $addressData['street'] ?? null,
                    $addressData['zipCode'] ?? null,
                    $addressData['city'] ?? null,
                    $addressData['country'] ?? null,
                    $isPrimary
                ]);
            }
        }
    }

    public function listBoardMembers(string $organizationId): array
    {
        $sql = "SELECT p.*, obm.role 
                FROM natural_persons p
                JOIN organization_board_members obm ON p.id = obm.naturalPersonId
                WHERE obm.organizationId = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$organizationId]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($members as &$member) {
            $stmt = $this->pdo->prepare("SELECT email, isPrimary FROM emails WHERE naturalPersonId = ?");
            $stmt->execute([$member['id']]);
            $member['emails'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT phoneNumber, isPrimary FROM phones WHERE naturalPersonId = ?");
            $stmt->execute([$member['id']]);
            $member['phones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT street, zipCode, city, country, isPrimary FROM addresses WHERE naturalPersonId = ?");
            $stmt->execute([$member['id']]);
            $member['addresses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->pdo->prepare("SELECT role FROM person_roles WHERE naturalPersonId = ?");
            $stmt->execute([$member['id']]);
            $member['roles'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return $members;
    }

    public function addBoardMember(string $organizationId, string $naturalPersonId, string $role): bool
    {
        $stmt = $this->pdo->prepare("INSERT OR REPLACE INTO organization_board_members (organizationId, naturalPersonId, role) VALUES (?, ?, ?)");
        return $stmt->execute([$organizationId, $naturalPersonId, $role]);
    }

    public function removeBoardMember(string $organizationId, string $naturalPersonId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM organization_board_members WHERE organizationId = ? AND naturalPersonId = ?");
        return $stmt->execute([$organizationId, $naturalPersonId]);
    }

    public function updateBoard(string $organizationId, array $data): bool
    {
        // Mock implementation
        return true;
    }

    public function listMembers(string $organizationId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT np.* FROM natural_persons np
            JOIN memberships m ON np.id = m.naturalPersonId
            WHERE m.organizationId = ?
        ");
        $stmt->execute([$organizationId]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($members as &$member) {
            $member['emails'] = $this->pdo->query("SELECT * FROM emails WHERE naturalPersonId = '{$member['id']}'")->fetchAll(PDO::FETCH_ASSOC);
            $member['phones'] = $this->pdo->query("SELECT * FROM phones WHERE naturalPersonId = '{$member['id']}'")->fetchAll(PDO::FETCH_ASSOC);
            $member['addresses'] = $this->pdo->query("SELECT * FROM addresses WHERE naturalPersonId = '{$member['id']}'")->fetchAll(PDO::FETCH_ASSOC);
            $member['roles'] = $this->pdo->query("SELECT role FROM person_roles WHERE naturalPersonId = '{$member['id']}'")->fetchAll(PDO::FETCH_COLUMN);
        }

        return $members;
    }

    public function addMember(string $organizationId, array $data): string
    {
        $naturalPersonId = $data['id'] ?? null;
        if (!$naturalPersonId) {
            $naturalPersonId = $this->createNaturalPerson($data);
        }

        $id = bin2hex(random_bytes(18));
        $stmt = $this->pdo->prepare("INSERT INTO memberships (id, naturalPersonId, organizationId, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $naturalPersonId, $organizationId, $data['status'] ?? 'Active']);

        return $id;
    }

    public function getMember(string $organizationId, string $memberId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM memberships WHERE id = ? AND organizationId = ?");
        $stmt->execute([$memberId, $organizationId]);
        $membership = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$membership) return null;

        $person = $this->getNaturalPerson($membership['naturalPersonId']);
        return array_merge($membership, $person ?: []);
    }

    public function updateMember(string $organizationId, string $memberId, array $data): bool
    {
        $fields = [];
        $params = [];
        
        $allowedFields = ['status', 'groupId', 'type'];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $params[] = $memberId;
            $params[] = $organizationId;
            $sql = "UPDATE memberships SET " . implode(', ', $fields) . " WHERE id = ? AND organizationId = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        }

        return true;
    }

    public function removeMember(string $organizationId, string $memberId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM memberships WHERE id = ? AND organizationId = ?");
        return $stmt->execute([$memberId, $organizationId]);
    }

    public function createSyllabus(array $data): string
    {
        $id = $data['id'] ?? bin2hex(random_bytes(18));
        $stmt = $this->pdo->prepare("INSERT INTO syllabuses (id, title, description, goals, prerequisites) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $id,
            $data['title'],
            $data['description'] ?? null,
            $data['goals'] ?? null,
            $data['prerequisites'] ?? null
        ]);
        return $id;
    }

    public function getSyllabus(string $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM syllabuses WHERE id = ?");
        $stmt->execute([$id]);
        $syllabus = $stmt->fetch(PDO::FETCH_ASSOC);
        return $syllabus ?: null;
    }

    public function updateSyllabus(string $id, array $data): bool
    {
        $fields = [];
        $params = [];
        
        $allowedFields = ['title', 'description', 'goals', 'prerequisites'];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $fields[] = "lastUpdate = CURRENT_TIMESTAMP";
            $params[] = $id;
            $sql = "UPDATE syllabuses SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        }

        return true;
    }

    public function listSyllabuses(): array
    {
        return $this->pdo->query("SELECT * FROM syllabuses")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createCourse(array $data): string
    {
        $id = $data['id'] ?? bin2hex(random_bytes(18));
        $detailedPlan = isset($data['detailedPlan']) ? json_encode($data['detailedPlan']) : null;
        $stmt = $this->pdo->prepare("INSERT INTO courses (id, syllabusId, teacher, speed, lectureCount, detailedPlan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $id,
            $data['syllabusId'],
            $data['teacher'],
            $data['speed'] ?? null,
            $data['lectureCount'] ?? null,
            $detailedPlan
        ]);
        return $id;
    }

    public function getCourse(string $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($course && isset($course['detailedPlan'])) {
            $course['detailedPlan'] = json_decode($course['detailedPlan'], true);
        }
        return $course ?: null;
    }

    public function listCourses(): array
    {
        $courses = $this->pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($courses as &$course) {
            if (isset($course['detailedPlan'])) {
                $course['detailedPlan'] = json_decode($course['detailedPlan'], true);
            }
        }
        return $courses;
    }

    public function listCoursesByOrganization(string $orgId): array
    {
        $sql = "SELECT c.*, s.title, s.description, s.goals, s.prerequisites 
                FROM courses c 
                JOIN syllabuses s ON c.syllabusId = s.id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($courses as &$course) {
            if (isset($course['detailedPlan'])) {
                $course['detailedPlan'] = json_decode($course['detailedPlan'], true);
            }
            $course['syllabus'] = [
                'id' => $course['syllabusId'],
                'title' => $course['title'],
                'description' => $course['description'],
                'goals' => $course['goals'],
                'prerequisites' => $course['prerequisites']
            ];
            // Remove the flat syllabus fields if desired, but keep them for now or remove to match expectation
            unset($course['title'], $course['description'], $course['goals'], $course['prerequisites']);
        }
        return $courses;
    }

    public function deleteCourse(string $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM courses WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function createBooking(array $data): string
    {
        $id = $data['id'] ?? bin2hex(random_bytes(18));
        $stmt = $this->pdo->prepare("INSERT INTO bookings (id, naturalPersonId, courseId, guardianName, guardianEmail, guardianPhone, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $id,
            $data['naturalPersonId'],
            $data['courseId'],
            $data['guardianName'] ?? null,
            $data['guardianEmail'] ?? null,
            $data['guardianPhone'] ?? null,
            $data['status'] ?? 'Pending'
        ]);
        return $id;
    }

    public function getBooking(string $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        return $booking ?: null;
    }

    public function updateBookingStatus(string $id, string $status): bool
    {
        $booking = $this->getBooking($id);
        if (!$booking) {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $result = $stmt->execute([$status, $id]);

        if ($result && $status === 'Confirmed') {
            // Automatically create a confirmation message for the guardian
            $person = $this->getNaturalPerson($booking['naturalPersonId']);
            $course = $this->getCourse($booking['courseId']);
            $syllabus = $course ? $this->getSyllabus($course['syllabusId']) : null;
            $courseName = $syllabus ? $syllabus['title'] : 'simkurs';

            $subject = "Bokningsbekräftelse: " . $courseName;
            $body = sprintf(
                "Hej %s,\n\nBokningen för %s på kursen %s är nu bekräftad.\n\nMed vänlig hälsning,\nKlubbadministrationen",
                $booking['guardianName'],
                $person ? $person['firstName'] : 'ditt barn',
                $courseName
            );

            $this->createMessage([
                'subject' => $subject,
                'body' => $body
            ]);
        }

        return $result;
    }

    public function listBookings(): array
    {
        return $this->pdo->query("SELECT * FROM bookings")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listBookingsForPerson(string $naturalPersonId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM bookings WHERE naturalPersonId = ?");
        $stmt->execute([$naturalPersonId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createNews(array $data): string
    {
        $id = $data['id'] ?? bin2hex(random_bytes(18));
        $stmt = $this->pdo->prepare("INSERT INTO news (id, organizationId, title, content, author, publishDate) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $id,
            $data['organizationId'],
            $data['title'],
            $data['content'],
            $data['author'] ?? null,
            $data['publishDate'] ?? null
        ]);
        return $id;
    }

    public function getNews(string $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM news WHERE id = ?");
        $stmt->execute([$id]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);
        return $news ?: null;
    }

    public function updateNews(string $id, array $data): bool
    {
        $news = $this->getNews($id);
        if (!$news) {
            return false;
        }

        $title = $data['title'] ?? $news['title'];
        $content = $data['content'] ?? $news['content'];
        $author = $data['author'] ?? $news['author'];
        $publishDate = $data['publishDate'] ?? $news['publishDate'];

        $stmt = $this->pdo->prepare("UPDATE news SET title = ?, content = ?, author = ?, publishDate = ?, updatedAt = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$title, $content, $author, $publishDate, $id]);
    }

    public function deleteNews(string $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM news WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function listNews(string $organizationId, bool $onlyPublished = false): array
    {
        $sql = "SELECT * FROM news WHERE organizationId = ?";
        $params = [$organizationId];

        if ($onlyPublished) {
            $sql .= " AND (publishDate IS NULL OR publishDate <= CURRENT_TIMESTAMP)";
        }

        $sql .= " ORDER BY publishDate DESC, createdAt DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listAllNews(bool $onlyPublished = false): array
    {
        $sql = "SELECT * FROM news";
        $params = [];

        if ($onlyPublished) {
            $sql .= " WHERE (publishDate IS NULL OR publishDate <= CURRENT_TIMESTAMP)";
        }

        $sql .= " ORDER BY publishDate DESC, createdAt DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}