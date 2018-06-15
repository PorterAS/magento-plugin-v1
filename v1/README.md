Convert Porterbuddy
===================
Porterbuddy is a delivery provider, and the module is a shipping method for the checkout process.

## Detect receiving payment - set date in order.pb_paid_at
- paymentPlaceAfterCheckPaid - for orders that authorize money immediately without redirects
- orderSaveBeforeCheckPaid - detect status change for orders updating after returning from external systems

## Order shipment via API
- shipmentSaveBeforeSubmitShipment - detect only new shipments, create shipment order in Porterbuddy

## Delivery coordinates geocoding
- required store coordinates in _Sales > Shipping Settings > Origin_

## Auto create shipment flows
- best case - order is paid, user selects location.
  shipment created in controller when saving location:

  ```paid_at=non-empty```

- deferred payment - user comes to success page and selects location, payment comes later.
  shipment created in cron immediately:

  ```paid_at=non-empty, user_edited=1, location=filled```

- location timeout case - order is paid, non-standard checkout or user is slowpoke
  shipment created in cron with for orders paid more than X minutes ago:

  ```paid_at=(X minutes ago), user_edited=0, location=empty/filled from address book.```


## Timeslot generation
2-step process:
- aligns to shop working hours, adds cutoff timeslot
- generates timeslots every 2 hours (configurable)
- if last timeslot if not full, overlaps its starting time with previous timeslot.
  E.g. for closing time 18:00, last timeslots can looks like - 15:00-17:00, 16:00-18:00

## Package calculation
- base units - kg and cm
- store products can have other units, e.g. gram and/or millimeters, they are converted to base units 
