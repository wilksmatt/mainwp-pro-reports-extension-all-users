# MainWP Pro Reports Extension - All Users

This plugin adds custom tokens to the MainWP Pro Reports extension, allowing you to display a list or table of all users from a child site in your Pro Reports. No code or plugin changes are required on the child sites themselves.

## Purpose
- Display all users from a connected child site in MainWP Pro Reports.
- Use the `[allusers]` token for a plain text list, or `[allusers.table]` for a formatted table.
- Works in custom report templates and custom sections.

## Installation
1. Download or clone this repository into your MainWP Dashboard site's `wp-content/plugins/` directory.
2. Ensure the folder is named `mainwp-pro-reports-extension-all-users`.
3. Activate the plugin from the WordPress Plugins admin page on your MainWP Dashboard site.
4. Make sure the MainWP Pro Reports extension is also installed and activated.

## Usage
- In your Pro Reports template (or custom section), insert the token `[allusers]` to display a plain text list of users, or `[allusers.table]` to display a table of users.
- The plugin will automatically fetch the user list from the selected child site and replace the token in the report output.
- **Note:** User data is fetched from the MainWP Dashboard site's database and will only include users that have been synced from the client site. Make sure to sync your client sites before generating reports to ensure the user list is up to date.
- No configuration is required.

## Example
```
[allusers]
```
Or:
```
[allusers.table]
```

## Requirements
- MainWP Dashboard (latest version recommended)
- MainWP Pro Reports extension (latest version recommended)

## Support
For issues or feature requests, please open an issue on the [GitHub repository](https://github.com/wilksmatt/mainwp-pro-reports-extension-all-users).
