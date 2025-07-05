<?php

namespace App\Http\Controllers\Api;

use App\Http\Actions\CreateContactAction;
use App\Http\Actions\UpdateContactAction;
use App\Http\Actions\DeleteContactAction;
use App\Http\Controllers\Controller;
use App\Http\Filters\ContactPipeline;
use App\Http\Requests\CreateContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use App\Services\ContactCacheService;
use App\Services\FeatureToggleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/contacts",
     *     operationId="getContactsList",
     *     tags={"Contacts"},
     *     summary="Get list of contacts",
     *     description="Returns list of contacts with pagination",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of contacts per page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort by field",
     *         required=false,
     *         @OA\Schema(type="string", enum={"name", "email", "phone", "created_at", "updated_at"})
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Sort direction",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Contact")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request, ContactCacheService $cacheService): JsonResponse
    {
        $perPage = $request->get('per_page', 10);
        
        // Generate cache key based on query parameters
        $cacheKey = $cacheService->generateCacheKey($request);
        
        // Try to get from cache first
        $contacts = $cacheService->remember($cacheKey, function () use ($request, $perPage) {
            $query = Contact::query();

            // Apply filters using Pipeline pattern
            $pipeline = new ContactPipeline($request);
            $query = $pipeline->apply($query);

            return $query->paginate($perPage);
        });

        return response()->json($contacts);
    }

    /**
     * @OA\Post(
     *     path="/api/contacts",
     *     operationId="storeContact",
     *     tags={"Contacts"},
     *     summary="Store a new contact",
     *     description="Create a new contact",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ContactStore")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contact created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Feature disabled",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Feature Disabled"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="feature_key", type="string")
     *         )
     *     )
     * )
     */
    public function store(CreateContactRequest $request, CreateContactAction $action, ContactCacheService $cacheService): JsonResponse
    {
        FeatureToggleService::ensureCanCreateContacts();

        $contact = $action->execute($request->validated());

        // Clear cache since a new contact was created
        $cacheService->clearAll();

        return response()->json($contact, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/contacts/{id}",
     *     operationId="getContactById",
     *     tags={"Contacts"},
     *     summary="Get contact information",
     *     description="Returns contact data",
     *     @OA\Parameter(
     *         name="id",
     *         description="Contact id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contact not found"
     *     )
     * )
     */
    public function show(Contact $contact): JsonResponse
    {
        return response()->json($contact);
    }

    /**
     * @OA\Put(
     *     path="/api/contacts/{id}",
     *     operationId="updateContact",
     *     tags={"Contacts"},
     *     summary="Update existing contact",
     *     description="Update contact data",
     *     @OA\Parameter(
     *         name="id",
     *         description="Contact id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ContactUpdate")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contact not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Feature disabled"
     *     )
     * )
     */
    public function update(UpdateContactRequest $request, Contact $contact, UpdateContactAction $action, ContactCacheService $cacheService): JsonResponse
    {
        FeatureToggleService::ensureCanUpdateContacts();

        $updatedContact = $action->execute($contact, $request->validated());

        // Clear cache since a contact was updated
        $cacheService->clearAll();

        return response()->json($updatedContact);
    }

    /**
     * @OA\Delete(
     *     path="/api/contacts/{id}",
     *     operationId="deleteContact",
     *     tags={"Contacts"},
     *     summary="Delete a contact",
     *     description="Delete contact data",
     *     @OA\Parameter(
     *         name="id",
     *         description="Contact id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Contact deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contact not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Feature disabled"
     *     )
     * )
     */
    public function destroy(Contact $contact, DeleteContactAction $action, ContactCacheService $cacheService): JsonResponse
    {
        FeatureToggleService::ensureCanDeleteContacts();

        $deleted = $action->execute($contact);

        if ($deleted) {
            // Clear cache since a contact was deleted
            $cacheService->clearAll();
            
            return response()->json(['message' => 'Contact deleted successfully']);
        }

        return response()->json(['message' => 'Failed to delete contact'], 500);
    }


}
