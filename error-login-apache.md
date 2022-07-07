# Add this method to you authenticator

```php
public function supports(Request $request): bool
{
    return $request->isMethod('POST') && '/login' === $request->getPathInfo();
}
```
