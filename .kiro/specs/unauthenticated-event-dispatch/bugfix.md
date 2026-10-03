# Bugfix Requirements: Unauthenticated Event Dispatch Exception

## Introduction

The /test-delegates route fails silently when dispatching the ApplicationStatusChanged event in an unauthenticated context. The route produces no browser console output because event listeners throw exceptions when trying to access Auth::id() without an authenticated user. This breaks the output buffer and prevents the delegate pattern demonstration from displaying results.

The bug occurs because LogApplicationActivity::handleStatusChanged() calls Auth::id() directly, which returns null in unauthenticated contexts, causing the ActivityLog::create() to fail and halting event listener execution.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN the /test-delegates route is accessed without authentication AND the ApplicationStatusChanged event is dispatched THEN LogApplicationActivity::handleStatusChanged() calls Auth::id() which returns null

1.2 WHEN Auth::id() returns null in the ActivityLog::create() method THEN a database constraint violation occurs (user_id cannot be null)

1.3 WHEN the listener throws an exception THEN the exception propagates up through the event dispatcher, breaking the output buffer and preventing console output display

1.4 WHEN the event dispatch fails silently THEN no visual feedback is provided to the user about listener execution status

### Expected Behavior (Correct)

2.1 WHEN the /test-delegates route is accessed without authentication AND the ApplicationStatusChanged event is dispatched THEN LogApplicationActivity::handleStatusChanged() SHALL check if Auth::id() is null before writing to ActivityLog

2.2 WHEN Auth::id() is null in an unauthenticated context THEN LogApplicationActivity::handleStatusChanged() SHALL gracefully skip logging by not attempting to create an ActivityLog entry

2.3 WHEN all listeners execute without throwing exceptions THEN the output buffer SHALL remain intact

2.4 WHEN the event dispatch completes successfully THEN the console output SHALL display all delegate execution status messages

### Unchanged Behavior (Regression Prevention)

3.1 WHEN the /test-delegates route is accessed WITH authentication by a logged-in user THEN LogApplicationActivity::handleStatusChanged() SHALL CONTINUE TO create an ActivityLog entry with the authenticated user's ID

3.2 WHEN Auth::id() returns a valid user ID in an authenticated context THEN the ActivityLog::create() method SHALL CONTINUE TO function without modification

3.3 WHEN the /test-delegates route dispatches the ApplicationStatusChanged event with other event listeners (SendStatusNotification, UpdateApplicationStatistics, CreateAwardForApprovedApplication) THEN those listeners SHALL CONTINUE TO execute without being affected by the LogApplicationActivity changes

3.4 WHEN the ApplicationSubmitted event triggers LogApplicationActivity::handleSubmitted() in an authenticated context THEN that handler SHALL CONTINUE TO log with the authenticated user's ID
