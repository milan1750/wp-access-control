# WP Platform Access Control

A WordPress plugin to manage multi-level access control for users, roles, and sites.

---

## Features

- Create entities and sites.
- Assign roles and capabilities to users.
- Super admin access for existing administrators.
- Supports global and scoped permissions.

---

## Installation

1. Upload the plugin to `/wp-content/plugins/`.
2. Activate it from the WordPress **Plugins** menu.
3. Tables will be created automatically on activation.

---

## Usage

- Administrators are automatically assigned `super_user` role.
- Manage roles, capabilities, and user access through the plugin.

---

## Database Tables

- `wpac_entities` – Entities.
- `wpac_sites` – Sites under entities.
- `wpac_user_capabilities` – User capabilities.
- `wpac_roles` – Custom roles.
- `wpac_role_capabilities` – Role-to-capability mapping.
- `wpac_user_roles` – Assign roles to users.
- `wpac_scopes` – Access scopes.

---

## License

GPL-2.0-or-later
