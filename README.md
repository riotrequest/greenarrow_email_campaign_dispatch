# GreenArrow Email Campaign Dispatch Script

This PHP script is designed to send email campaigns using the GreenArrow Email API. It fetches HTML content from specified URLs, extracts relevant information, and dispatches email campaigns to a mailing list segment.

## Usage

1. Configure the script with your GreenArrow Email API credentials:
   - `GAS_BASE_URL`: The base URL for the GreenArrow API.
   - `GAS_API_KEY`: Your API key (base64-encoded).

2. Customize the email campaign parameters in the `$dispatch_parameters` array to suit your requirements. You can specify the mailing list ID, segmentation criteria, dispatch attributes, and email content.

3. The script fetches HTML content from two URLs and extracts subject, name, and text content.

4. It schedules email dispatches using the GreenArrow Email API with the provided parameters.

5. The results of each email dispatch are displayed in the script's output.

## Requirements

- PHP with cURL support
- GreenArrow Email API credentials

## Configuration

- Adjust the URLs in the `$html1` and `$html2` variables to point to the desired HTML content sources.
- Customize the `$dispatch_parameters` array with your campaign details, including mailing list ID, segmentation criteria, dispatch attributes, and content.

## Example

The provided PHP code demonstrates how to fetch HTML content, extract information, and dispatch email campaigns using the GreenArrow Email API. It dispatches email campaigns based on the content extracted from two templates.

## License

This script is available under the [MIT License](LICENSE).

Feel free to use, modify, and distribute it according to your requirements. If you encounter any issues or have suggestions for improvements, please open an issue or contribute to the project.
