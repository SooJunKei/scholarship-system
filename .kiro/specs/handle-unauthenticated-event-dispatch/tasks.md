# Implementation Plan

## Overview

This implementation plan follows the bug condition methodology to fix the unauthenticated event dispatch bug. Tasks are ordered to explore the bug first, verify preservation behavior, then implement and validate the fix.

---

## Phase 1: Explore Bug Condition

- [ ] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - Graceful Null Auth Handling in Event Listeners
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bug exists when events are dispatched without authentication
  - **Scoped PBT Approach**: Scope the property to concrete failing case: dispatch ApplicationStatusChanged event with Auth::id() = null
  
  Test implementation details from Bug Condition in design:
  - Verify that `LogApplicationActivity::handleStatusChanged()` receives ApplicationStatusChanged events
  - Test dispatching event without authenticated user (Auth::id() is null)
  - Assert that the listener completes execution without throwing exceptions or causing silent failures
  - Assert that no ActivityLog record is created when Auth::id() is null
  - Test assertions should match the Expected Behavior Properties from design: "event handler completes without errors, gracefully skips logging"
  
  **Implementation Details**:
  - Create test case in appropriate test directory (tests/Feature or tests/Unit)
  - Use Laravel's event testing capabilities (Event::fake, dispatch)
  - Mock authentication to return null via Auth::shouldReceive('id')->andReturn(null)
  - Verify no ActivityLog records exist after event dispatch
  - Document any exceptions thrown on unfixed code
  
  Run test on UNFIXED code (before implementing the fix)
  
  **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bug exists)
  - Document counterexamples found: "ApplicationStatusChanged event with Auth::id()=null causes listener to [fail/skip/throw exception]"
  - Examine the failure to understand the root cause
  - Record the specific error or behavior observed
  
  Mark task complete when test is written, run on unfixed code, and failure is documented
  _Requirements: 2.1, 2.2_

---

## Phase 2: Preserve Existing Behavior

- [ ] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Authenticated Logging Behavior is Preserved
  - **IMPORTANT**: Follow observation-first methodology
  - **Observe on UNFIXED code**: When ApplicationStatusChanged or ApplicationSubmitted events are dispatched with an authenticated user, verify that ActivityLog records ARE created with the correct user_id
  - Observe: handleStatusChanged with authenticated user creates ActivityLog with user_id matching Auth::id()
  - Observe: handleSubmitted with authenticated user creates ActivityLog with user_id matching Auth::id()
  - Observe: application and scholarship relationships are properly loaded and included in description
  
  Write property-based test from Preservation Requirements in design:
  - For all events dispatched with Auth::id() returning a valid user ID
  - Assert: ActivityLog record is created
  - Assert: ActivityLog.user_id equals Auth::id()
  - Assert: ActivityLog.subject_type and subject_id reference the correct application
  - Assert: ActivityLog.event and description contain expected values
  
  **Implementation Details**:
  - Create test case using property-based testing framework (e.g., Pest with property-based testing, or PHPUnit data providers)
  - Generate multiple authenticated user IDs (1, 2, 5, 100, etc.) to test with
  - For each user ID, dispatch both ApplicationStatusChanged and ApplicationSubmitted events
  - Verify ActivityLog records are created with the correct user_id
  - Property-based testing generates many test cases for stronger guarantees
  - Test edge cases: different application states, different scholarship configurations, concurrent events
  
  Verify test passes on UNFIXED code
  
  **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Record the observed behavior patterns
  - Document that logging works correctly for authenticated users on unfixed code
  
  Mark task complete when tests are written, run on unfixed code, and passing
  _Requirements: 3.1, 3.2, 3.3_

---

## Phase 3: Implement the Fix

- [ ] 3. Fix the unauthenticated event dispatch bug

  - [ ] 3.1 Implement the fix
    - Add null check guard clause to `LogApplicationActivity::handleStatusChanged()`
    - Add guard clause: `if (Auth::id() === null) return;` at the beginning of the method (before loading relationships)
    - This prevents ActivityLog::create() from being called when no user is authenticated
    
    - Add null check guard clause to `LogApplicationActivity::handleSubmitted()`
    - Add guard clause: `if (Auth::id() === null) return;` at the beginning of the method (before loading relationships)
    - This ensures consistent behavior across both event handlers
    
    - File to modify: `app/Listeners/LogApplicationActivity.php`
    - Both methods should skip loading relationships and ActivityLog creation when Auth::id() is null
    - Ensure the fix is minimal and doesn't alter authenticated logging behavior
    
    _Bug_Condition: isBugCondition(input) where Auth::id() IS NULL AND event listener attempts to call ActivityLog::create()_
    _Expected_Behavior: event handler completes execution without errors, gracefully skips logging when no user context exists_
    _Preservation: All authenticated event dispatches must continue logging activities with the correct user_id as before_
    _Requirements: 2.1, 2.2, 3.1, 3.2, 3.3_

  - [ ] 3.2 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Graceful Null Auth Handling in Event Listeners
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Run the bug condition exploration test from step 1 with the fixed code
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - The listener should now handle null Auth::id() gracefully without errors
    - Verify no ActivityLog records are created for unauthenticated events
    - Verify the /test-delegates route now works without authentication
    _Requirements: 2.1, 2.2_

  - [ ] 3.3 Verify preservation tests still pass
    - **Property 2: Preservation** - Authenticated Logging Behavior is Preserved
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run the preservation property tests from step 2 with the fixed code
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Confirm all authenticated event dispatches still create ActivityLog records correctly
    - Verify user_id values are correct for all authenticated scenarios
    - Confirm all tests still pass after fix (no regressions in authenticated logging)

---

## Phase 4: Checkpoint

- [ ] 4. Checkpoint - Ensure all tests pass
  - Run all tests from tasks 1, 2, and 3
  - Verify bug condition test passes (task 1)
  - Verify preservation tests pass (task 2)
  - Verify no regressions in authenticated logging behavior
  - Verify /test-delegates route works correctly without authentication
  - Test the /test-delegates route in browser to confirm visual functionality
  - Confirm all event handlers execute normally with and without authentication
  - All tests passing indicates the bugfix is complete and correct
