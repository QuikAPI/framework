<?php
namespace QuikAPI\Controllers;

use QuikAPI\Http\Request;
use QuikAPI\Support\Responses;
use QuikAPI\Support\QueryOps;
use QuikAPI\Models\User as Model;
use QuikAPI\Requests\User\IndexRequest;
use QuikAPI\Requests\User\StoreRequest;
use QuikAPI\Requests\User\UpdateRequest;
use QuikAPI\Security\Password;

class UserController
{
    public function index(Request $request): array
    {
        try {
            $validated = (new IndexRequest())->validated($request);
            $perPage = $validated['per_page'] ?? 25;
            $operations = $validated['operations'] ?? [];
            $select = $validated['select'] ?? [];
            $orderBy = $validated['order_by'] ?? 'id';
            $orderType = $validated['order_type'] ?? 'desc';
            $groupBy = $validated['group_by'] ?? null;
            $returnType = $validated['return_type'] ?? 'data';
            $with = $validated['with'] ?? '';

            $query = Model::query();
            if ($select) { $query->select($select); }
            $user = $request->authUser;
            if ($user) { $query->where('user_id', $user->id); }
            $query = QueryOps::addOperationsInQuery($query, $operations);
            if ($groupBy) { $query->groupBy($groupBy); }
            if ($orderBy) { $query->orderBy($orderBy, $orderType); }

            if ($returnType === 'count') {
                $data = $query->count();
            } else {
                $data = $query->paginate($perPage);
                if ($with) { $rels = explode(',', $with); $data->load($rels); }
            }
            return Responses::success($data, 200);
        } catch (\Illuminate\Database\QueryException $ex) {
            return Responses::fail([$ex->getMessage()], 500);
        } catch (\Throwable $ex) {
            return Responses::fail([$ex->getMessage()], 422);
        }
    }

    public function show(Request $request): array
    {
        try {
            $id = (int)($request->params['id'] ?? 0);
            $with = $request->input('with', '');
            $m = Model::query();
            if ($with) { $m->with(explode(',', $with)); }
            $row = $m->findOrFail($id);
            return Responses::success($row, 200);
        } catch (\Throwable $ex) {
            return Responses::fail([$ex->getMessage()], 404);
        }
    }

    public function store(Request $request): array
    {
        try {
            $data = (new StoreRequest())->validated($request);
            $data['password'] = Password::hash($data['password']);
            $row = Model::create($data);
            return Responses::success($row, 201);
        } catch (\Throwable $ex) {
            return Responses::fail([$ex->getMessage()], 422);
        }
    }

    public function update(Request $request): array
    {
        try {
            $id = (int)($request->params['id'] ?? 0);
            $data = (new UpdateRequest())->validated($request);
            if (isset($data['password'])) {
                $data['password'] = Password::hash($data['password']);
            }
            $row = Model::findOrFail($id);
            $row->fill($data);
            $row->save();
            return Responses::success($row, 200);
        } catch (\Throwable $ex) {
            return Responses::fail([$ex->getMessage()], 422);
        }
    }

    public function destroy(Request $request): array
    {
        try {
            $id = (int)($request->params['id'] ?? 0);
            $row = Model::findOrFail($id);
            $row->delete();
            return Responses::success(['deleted' => $id], 200);
        } catch (\Throwable $ex) {
            return Responses::fail([$ex->getMessage()], 422);
        }
    }
}
