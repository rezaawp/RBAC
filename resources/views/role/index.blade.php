@extends('panel.layout.app', ['disable_tblr' => true])

@section('title', __('Role'))
@section('titlebar_actions')
    <x-modal
        title="{{ __('Add new role') }}"
        disable-modal="{{ $app_is_demo }}"
        disable-modal-message="{{ __('This feature is disabled in Demo version.') }}"
    >
        <x-slot:trigger>
            {{ __('Add New') }}
        </x-slot:trigger>
        <x-slot:modal>
            <form
                action="{{ route('dashboard.admin.rbac.roles.store') }}"
                method="POST"
            >
                @csrf

                <x-forms.input
                    id="role"
                    name="role"
                    size="lg"
                    label="{{ __('Role Name') }}"
                    required
                />

                <x-forms.input
                    id="label"
                    name="label"
                    size="lg"
                    label="{{ __('Label') }}"
                    required
                />

                <div class="mt-4 border-t pt-3">
                    <x-button
                        @click.prevent="modalOpen = false"
                        variant="outline"
                        type="button"
                    >
                        {{ __('Cancel') }}
                    </x-button>
                    <x-button type="submit">
                        {{ __('Save changes') }}
                    </x-button>
                </div>
            </form>
        </x-slot:modal>
    </x-modal>
@endsection
@section('content')
    <div class="py-10">
        <h2 class="mb-5">
            {{ __('Roles') }}
        </h2>

        <x-table>
            <x-slot:head>
                <tr>
                    <th>
                        {{ __('Name') }}
                    </th>
                    <th>
                        {{ __('Permissions') }}
                    </th>
                    <th class="text-end">
                        {{ __('Actions') }}
                    </th>
                </tr>
            </x-slot:head>

            <x-slot:body>
                @foreach ($items as $item)
                    <tr>
                        <td>
                            {{ $item->name }}
                        </td>
                        <td>
                            {{ $item->permissions }}
                        </td>
                        <td class="whitespace-nowrap text-end">
                            @if ($app_is_demo)
                                <x-button
                                    class="size-9"
                                    variant="ghost-shadow"
                                    size="none"
                                    onclick="return toastr.info('This feature is disabled in Demo version.')"
                                    title="{{ __('Edit') }}"
                                >
                                    <x-tabler-pencil class="size-4" />
                                </x-button>
                                <x-button
                                    class="size-9"
                                    variant="ghost-shadow"
                                    hover-variant="danger"
                                    size="none"
                                    onclick="return toastr.info('This feature is disabled in Demo version.')"
                                    title="{{ __('Delete') }}"
                                >
                                    <x-tabler-x class="size-4" />
                                </x-button>
                            @else
                                <x-button
                                    class="size-9"
                                    variant="ghost-shadow"
                                    size="none"
                                    href="{{ route('dashboard.admin.rbac.roles.edit', $item->id) }}"
                                    title="{{ __('Edit') }}"
                                >
                                    <x-tabler-pencil class="size-4" />
                                </x-button>
                                <x-button
                                    class="size-9"
                                    variant="ghost-shadow"
                                    hover-variant="danger"
                                    size="none"
                                    onclick="return confirm('{{ __('Are you sure? This is permanent and will delete all documents related to user.') }}')"
                                    href="{{ route('dashboard.admin.rbac.roles.destroy', $item->id) }}"
                                    title="{{ __('Delete') }}"
                                >
                                    <x-tabler-x class="size-4" />
                                </x-button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>

        <div class="mt-5 flex items-center justify-end">
            {{ $items->links() }}
        </div>
    </div>
@endsection
