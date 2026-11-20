# Security Specification & Hardening Plan

This document specifies the data invariants, threat model, and "Dirty Dozen" payload checks to secure our application's Firestore database.

## 1. Data Invariants

1. **User Identity Invariant**: A user's profile, logged meals, and custom ingredients can only be accessed or modified by the authenticated user whose `uid` matches the document path segment `{userId}`.
2. **Relational Sync Invariant**: Any sub-resource (e.g., custom ingredients or logged meals) of `{userId}` must contain a matching `userId` field that strictly equals the authenticated user's `uid`.
3. **Data Type Safety**: Every field (e.g., calories, protein, age) must be typed correctly and bounded. Out-of-bounds numbers or bloated string attacks must be rejected.
4. **Timestamp Integrity**: All `createdAt` or `updatedAt` values must be assigned matching `request.time` (Server Time), preventing client spoofing.

## 2. Threat Model: The "Dirty Dozen" Payloads

Here are 12 malicious payloads designed to break our database security. Our security rules are structured to block all of them:

1. **Spoofed User Identity on Profile**: User A tries to modify Profile of User B at `/users/UserB`.
2. **Missing Auth Profiles**: An unauthenticated user tries to read or write any `/users` profiles.
3. **Ghost Fields injection in UserProfile**: Trying to update `/users/UserA` with structural bypass like `{ "weight": 70, "extraGhostField": true }`.
4. **Out-of-Bounds weight on Profile**: Trying to save `{ "weight": -50, ... }` or `{ "weight": 9999, ... }`.
5. **PII Blanket Read Attack**: Requesting profile records for all users in `/users`.
6. **Orphaned LoggedMeal**: User A logs a meal to User A's subcollection but specifies `userId` as `UserB` (identity spoofing).
7. **Type Poisoning in LoggedMeal**: Attempting to write a logged meal where `calories` is a boolean or string instead of a number.
8. **Bloated ID Injection Attack**: Writing a meal with an ID of 2KB in size to trigger Denial-of-Wallet index bloat.
9. **Backdated Timestamps on Custom ingredients**: User specifies a static backdated timestamp for `createdAt` instead of `request.time`.
10. **Spoofed CustomIngredient Owner**: Creating a custom ingredient under User A's subcollection with a different owner ID.
11. **Negative Macros on Food Logs**: Setting a LoggedMeal with negative protein amount `-15g` to distort calculations.
12. **Malicious Regex Injection on ID segment**: Attempting to write a path containing malicious slash combinations or characters not matching `'^[a-zA-Z0-9_\-]+$'`.

## 3. Threat Mitigation Rules Verification

The `firestore.rules` file enforces verification on all these dimensions.
