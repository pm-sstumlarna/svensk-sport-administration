<?php

namespace SSA\library;

interface StorageServiceInterface
{
    public function check(): bool;
    public function startTransaction(): bool;
    public function commitTransaction(): bool;
    public function rollbackTransaction(): bool;

    public function initialize(): void;
    public function migrate(): void;
    public function seedExampleData(): void;

    // Person CRUD
    public function createNaturalPerson(array $data): string;
    public function getNaturalPerson(string $id): ?array;
    public function updateNaturalPerson(string $id, array $data): bool;
    public function listNaturalPersons(): array;

    // Activity CRUD
    public function createActivity(array $data): string;
    public function getActivity(string $id): ?array;
    public function updateActivity(string $id, array $data): bool;
    public function listActivities(): array;

    // Invitations
    public function createInvitation(string $activityId, array $data): bool;
    public function updateInvitation(string $activityId, string $naturalPersonId, array $data): bool;

    // Attendance
    public function getAttendance(string $activityId): array;
    public function updateAttendance(string $activityId, array $data): bool;

    // Goals
    public function createGoal(string $naturalPersonId, array $data): string;
    public function listGoals(string $naturalPersonId): array;

    // Invoices
    public function listInvoices(): array;
    public function createInvoices(): bool;

    // Reports
    public function getLokSupportReport(): array;

    // Groups
    public function listGroups(): array;

    // Messages
    public function createMessage(array $data): bool;

    // Fee levels (FK3.3)
    public function listFeeLevels(): array;
    public function createFeeLevel(array $data): string;

    // Statistics (FK5.2)
    public function getMemberStatistics(): array;

    // Organization
    public function listOrganizations(): array;
    public function createOrganization(array $data): string;
    public function getOrganization(string $id): ?array;
    public function updateOrganization(string $id, array $data): bool;

    // Organization Board Members
    public function listBoardMembers(string $organizationId): array;
    public function addBoardMember(string $organizationId, string $naturalPersonId, string $role): bool;
    public function removeBoardMember(string $organizationId, string $naturalPersonId): bool;
    public function updateBoard(string $organizationId, array $data): bool;

    // Organization Members
    public function listMembers(string $organizationId): array;
    public function addMember(string $organizationId, array $data): string;
    public function getMember(string $organizationId, string $memberId): ?array;
    public function updateMember(string $organizationId, string $memberId, array $data): bool;
    public function removeMember(string $organizationId, string $memberId): bool;

    // Syllabus CRUD
    public function createSyllabus(array $data): string;
    public function getSyllabus(string $id): ?array;
    public function updateSyllabus(string $id, array $data): bool;
    public function listSyllabuses(): array;

    // Course CRUD
    public function createCourse(array $data): string;
    public function getCourse(string $id): ?array;
    public function deleteCourse(string $id): bool;
    public function listCourses(): array;
    public function listCoursesByOrganization(string $orgId): array;

    // Bookings
    public function createBooking(array $data): string;
    public function getBooking(string $id): ?array;
    public function updateBookingStatus(string $id, string $status): bool;
    public function listBookings(): array;
    public function listBookingsForPerson(string $naturalPersonId): array;

    // News CRUD
    public function createNews(array $data): string;
    public function getNews(string $id): ?array;
    public function updateNews(string $id, array $data): bool;
    public function deleteNews(string $id): bool;
    public function listNews(string $organizationId, bool $onlyPublished = false): array;
    public function listAllNews(bool $onlyPublished = false): array;
}