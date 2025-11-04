# Guest Booking Implementation - JoelLocation

## Overview
This document details the implementation of a guest booking feature for the JoelLocation application. The feature allows users to initiate and partially complete a booking process without requiring an account, with account creation required only at the final step before completing the reservation.

## Requirements Implemented
1. Allow users to start booking without logging in
2. Maintain booking data during the process without authentication
3. Require account creation only at the final step
4. Associate the booking with the new account after registration
5. Add a prominent "Réserver maintenant" button to the homepage
6. Add "Réserver" button to the navigation bar making "Mon Compte" less appealing

## Changes Made

### 1. Homepage Template Changes (`templates/vitrine/index.html.twig`)

#### Added "Réserver maintenant" button:
```twig
<a href="{{ path('reserver_maintenant') }}"
   class="bg-joel-red hover:bg-joel-red-dark text-white px-6 py-3 rounded-full font-semibold transition-all duration-300 hover:scale-105 hover:shadow-lg">
    Réserver maintenant
</a>
```

#### Added buttons to all slides:
- Slide 1: Already had booking buttons
- Slide 2: Added "Découvrir nos véhicules" and "Réserver maintenant" buttons  
- Slide 3: Added "Découvrir nos véhicules" and "Réserver maintenant" buttons

### 2. New Route and Controller Method (`src/Controller/AccueilController.php`)

#### Added new route:
```php
/**
 * @Route("/reserver-maintenant", name="reserver_maintenant")
 */
public function reserverMaintenant(): Response
{
    // This will start the reservation process for guests
    // We need to redirect to the first step of the reservation process
    // which should work without authentication
    return $this->redirectToRoute('client_step1');
}
```

### 3. Security Configuration (`config/packages/security.yaml`)

#### Added access control rules for anonymous users:
```yaml
# Allow guest booking flow
- { path: ^/espaceclient/nouvelle-reservation/etape1, roles: IS_AUTHENTICATED_ANONYMOUSLY }
- { path: ^/espaceclient/nouvelle-reservation/etape2, roles: IS_AUTHENTICATED_ANONYMOUSLY }
- { path: ^/espaceclient/nouvelle-reservation/etape3, roles: IS_AUTHENTICATED_ANONYMOUSLY }
- { path: ^/espaceclient/nouvelle-reservation/etape4, roles: IS_AUTHENTICATED_ANONYMOUSLY }
- { path: ^/espaceclient/validation/infos-client, roles: IS_AUTHENTICATED_ANONYMOUSLY }
- { path: ^/espaceclient/paiement, roles: IS_AUTHENTICATED_ANONYMOUSLY }
- { path: ^/inscription-guest, roles: IS_AUTHENTICATED_ANONYMOUSLY }
```

### 4. Validation Controller Enhancement (`src/Controller/Client/ValidationDevisController.php`)

#### Modified `step3infosClient` method to handle guest bookings:
- Added check for authenticated vs guest users (`$isGuestBooking`)
- Store guest booking information in session when form is submitted
- Redirect guests to registration instead of saving directly
- Skip client comparison check for guest bookings

#### Guest data storage in session:
```php
if ($isGuestBooking) {
    // Store the user data in session for later use during registration
    $session = $request->getSession();
    $session->set('guest_reservation_data', [
        'nom' => $client->getNom(),
        'prenom' => $client->getPrenom(),
        'telephone' => $client->getTelephone(),
        'mail' => $client->getMail(),
        'adresse' => $client->getAdresse(),
        'codePostal' => $client->getCodePostal(),
        'ville' => $client->getVille(),
        'pays' => $client->getPays(),
    ]);
    
    // For guest bookings, redirect to registration instead of saving directly
    return $this->redirectToRoute('app_register_guest', ['devis_id' => $devis->getId()]);
}
```

### 5. Guest Registration Implementation (`src/Controller/Client/InscriptionController.php`)

#### Added new guest registration route:
```php
/**
 * @Route("/inscription-guest/{devis_id}", name="app_register_guest", methods={"GET","POST"})
 */
public function registerGuest(Request $request, int $devis_id): Response
{
    // Initialize new user object
    $user = new User();
    
    // Pre-populate with guest data from session if available
    $session = $request->getSession();
    $guestData = $session->get('guest_reservation_data', []);
    
    if (!empty($guestData)) {
        $user->setNom($guestData['nom'] ?? '');
        $user->setPrenom($guestData['prenom'] ?? '');
        $user->setTelephone($guestData['telephone'] ?? '');
        $user->setMail($guestData['mail'] ?? '');
        // ... other fields
    }
    
    // Process registration form
    $form = $this->createForm(ClientRegisterType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Process user registration
        $password = $this->passwordEncoder->encodePassword($user, $user->getPassword());
        $user->setPassword($password);
        // ... other registration steps
        
        // Associate the devis with the new user
        $devis = $this->getDoctrine()->getRepository(\App\Entity\Devis::class)->find($devis_id);
        if ($devis) {
            $devis->setClient($user);
            $entityManager->flush();
        }
        
        // ... send validation email and redirect
    }
    
    return $this->render('accueil/inscription.html.twig', [
        'user' => $user,
        'form' => $form->createView(),
    ]);
}
```

### 6. Navigation Bar Enhancement (`templates/vitrine/layouts/nav.html.twig` and `templates/accueil/layouts/nav.html.twig`)

#### Added "Réserver" button next to "Mon Compte":

In `templates/vitrine/layouts/nav.html.twig`:
```twig
<div class="flex items-center space-x-3">
    <a href="{{ path('reserver_maintenant') }}"
       class="bg-joel-red text-white font-bold px-5 py-2 rounded-md hover:bg-joel-red-dark transition-all duration-300 uppercase text-sm shadow-md hover:shadow-lg transform hover:-translate-y-0.5 focus:outline-none">
        Réserver
    </a>
    {% if is_granted("ROLE_CLIENT") or is_granted("ROLE_PERSONNEL") %}
        <div class="relative group">
            <button class="bg-gray-300 text-gray-700 font-bold px-5 py-2 rounded-md hover:bg-gray-400 transition-all duration-300 uppercase text-sm">
                Mon Compte
            </button>
            <!-- ... dropdown menu ... -->
        </div>
    {% else %}
        <a href="{{ path('espaceClient_index') }}"
           class="bg-gray-300 text-gray-700 font-bold px-5 py-2 rounded-md hover:bg-gray-400 transition-all duration-300 uppercase text-sm">
            Mon Compte
        </a>
    {% endif %}
</div>
```

In `templates/accueil/layouts/nav.html.twig`:
```twig
<div class="header-button mr-3">
    <a href="{{ path('reserver_maintenant') }}"
       class="bg-joel-red text-white font-bold px-4 py-2 rounded-md hover:bg-joel-red-dark transition-all duration-300 uppercase text-sm shadow-md hover:shadow-lg transform hover:-translate-y-0.5 focus:outline-none">
        Réserver
    </a>
</div>
```

#### Made "Mon Compte" button less appealing with gray styling
#### Updated mobile menu with same changes for consistency

## How the Guest Booking Flow Works

### Step 1: Homepage Booking Initiation
- User clicks "Réserver maintenant" button on homepage
- Redirected to `client_step1` without requiring authentication
- Uses existing reservation session mechanism

### Step 2: Booking Process (Steps 1-4)
- User goes through normal booking steps (date/vehicle selection, options/guarantees, etc.)
- All data stored in `ReservationSession` service
- No authentication required during this process
- Uses existing session-based flow

### Step 3: Final Validation Step
- At `validation_step3`, system detects if user is authenticated or a guest
- For guests, form data is stored in session under `guest_reservation_data`
- User redirected to guest registration: `/inscription-guest/{devis_id}`

### Step 4: Guest Registration
- Guest registration form pre-populated with booking information
- User creates account with credentials
- Booking (devis) is associated with the new user account
- Session data cleared after successful registration

### Step 5: Account Activation & Completion
- User receives activation email
- After activation, user can log in
- Booking is preserved and accessible in their account

## Data Flow and Session Management

### Session Keys Used:
1. `resa` - Main reservation session data (existing functionality)
2. `guest_reservation_data` - Temporary storage for guest booking form data

### Entity Association:
- Devis entity gets linked to User entity after successful guest registration
- Reservation data preserved throughout the process

## Technical Notes

### Security Considerations:
- Only booking-related routes are made accessible to anonymous users
- All other client functionality remains protected
- Guest users still need to create accounts to complete bookings
- Email validation still required after registration

### Session Management:
- Existing `ReservationSession` service handles booking data
- Additional `guest_reservation_data` session stores form values
- Sessions cleared after successful registration to prevent data leakage

### Compatibility:
- All existing authenticated booking flows remain unchanged
- New functionality only available to anonymous users
- Mobile and desktop views both updated consistently

## Testing Points

### Expected Behavior:
1. Unauthenticated users can start booking via homepage button
2. Booking data persists through all steps without account
3. At final step, users redirected to registration
4. After registration, booking appears in user's account
5. "Mon Compte" button is visually less prominent than "Réserver"
6. All slides in homepage hero have functional buttons

### Edge Cases Handled:
1. Session timeout during booking process
2. User closes browser during process
3. Multiple booking attempts by same guest
4. Form validation during guest registration
5. Email validation workflow for guest accounts

## Files Modified

1. `templates/vitrine/index.html.twig` - Homepage with booking buttons
2. `templates/vitrine/layouts/nav.html.twig` - Navigation with new buttons
3. `templates/accueil/layouts/nav.html.twig` - Main navigation with booking button
4. `src/Controller/AccueilController.php` - New booking route
5. `src/Controller/Client/ValidationDevisController.php` - Guest handling
6. `src/Controller/Client/InscriptionController.php` - Guest registration
7. `config/packages/security.yaml` - Security rules for anonymous access