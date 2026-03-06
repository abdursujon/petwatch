 <?php
  require_once(__DIR__ . '/../Models/Database.php');

  $db = Database::getInstance()->getdbConnection();

  // 40 Manchester locations, then UK cities for the rest
  $locations = [
      // Manchester (40)
      ['lat' => 53.4808, 'lng' => -2.2426, 'city' => 'Manchester'],
      ['lat' => 53.4831, 'lng' => -2.2441, 'city' => 'Manchester'],
      ['lat' => 53.4774, 'lng' => -2.2310, 'city' => 'Manchester'],
      ['lat' => 53.4796, 'lng' => -2.2485, 'city' => 'Manchester'],
      ['lat' => 53.4729, 'lng' => -2.2527, 'city' => 'Manchester'],
      ['lat' => 53.4760, 'lng' => -2.2536, 'city' => 'Manchester'],
      ['lat' => 53.4838, 'lng' => -2.2365, 'city' => 'Manchester'],
      ['lat' => 53.4852, 'lng' => -2.2398, 'city' => 'Manchester'],
      ['lat' => 53.4790, 'lng' => -2.2580, 'city' => 'Manchester'],
      ['lat' => 53.4745, 'lng' => -2.2690, 'city' => 'Manchester'],
      ['lat' => 53.4812, 'lng' => -2.2345, 'city' => 'Manchester'],
      ['lat' => 53.4765, 'lng' => -2.2412, 'city' => 'Manchester'],
      ['lat' => 53.4701, 'lng' => -2.2378, 'city' => 'Manchester'],
      ['lat' => 53.4680, 'lng' => -2.2290, 'city' => 'Manchester'],
      ['lat' => 53.4822, 'lng' => -2.2510, 'city' => 'Manchester'],
      ['lat' => 53.4755, 'lng' => -2.2622, 'city' => 'Manchester'],
      ['lat' => 53.4870, 'lng' => -2.2330, 'city' => 'Manchester'],
      ['lat' => 53.4695, 'lng' => -2.2465, 'city' => 'Manchester'],
      ['lat' => 53.4840, 'lng' => -2.2270, 'city' => 'Manchester'],
      ['lat' => 53.4720, 'lng' => -2.2350, 'city' => 'Manchester'],
      ['lat' => 53.4635, 'lng' => -2.2910, 'city' => 'Manchester'],
      ['lat' => 53.4650, 'lng' => -2.2800, 'city' => 'Manchester'],
      ['lat' => 53.4715, 'lng' => -2.2740, 'city' => 'Manchester'],
      ['lat' => 53.4780, 'lng' => -2.2200, 'city' => 'Manchester'],
      ['lat' => 53.4860, 'lng' => -2.2180, 'city' => 'Manchester'],
      ['lat' => 53.4900, 'lng' => -2.2350, 'city' => 'Manchester'],
      ['lat' => 53.4670, 'lng' => -2.2650, 'city' => 'Manchester'],
      ['lat' => 53.4590, 'lng' => -2.2720, 'city' => 'Manchester'],
      ['lat' => 53.4550, 'lng' => -2.2540, 'city' => 'Manchester'],
      ['lat' => 53.4880, 'lng' => -2.2500, 'city' => 'Manchester'],
      ['lat' => 53.4735, 'lng' => -2.2150, 'city' => 'Manchester'],
      ['lat' => 53.4620, 'lng' => -2.2480, 'city' => 'Manchester'],
      ['lat' => 53.4910, 'lng' => -2.2410, 'city' => 'Manchester'],
      ['lat' => 53.4570, 'lng' => -2.2310, 'city' => 'Manchester'],
      ['lat' => 53.4660, 'lng' => -2.2560, 'city' => 'Manchester'],
      ['lat' => 53.4530, 'lng' => -2.2680, 'city' => 'Manchester'],
      ['lat' => 53.4845, 'lng' => -2.2610, 'city' => 'Manchester'],
      ['lat' => 53.4710, 'lng' => -2.2830, 'city' => 'Manchester'],
      ['lat' => 53.4925, 'lng' => -2.2290, 'city' => 'Manchester'],
      ['lat' => 53.4685, 'lng' => -2.2130, 'city' => 'Manchester'],

      // London (25)
      ['lat' => 51.5074, 'lng' => -0.1278, 'city' => 'London'],
      ['lat' => 51.5155, 'lng' => -0.1420, 'city' => 'London'],
      ['lat' => 51.5033, 'lng' => -0.1195, 'city' => 'London'],
      ['lat' => 51.5194, 'lng' => -0.1270, 'city' => 'London'],
      ['lat' => 51.5115, 'lng' => -0.0988, 'city' => 'London'],
      ['lat' => 51.5225, 'lng' => -0.1544, 'city' => 'London'],
      ['lat' => 51.4995, 'lng' => -0.1248, 'city' => 'London'],
      ['lat' => 51.5290, 'lng' => -0.1228, 'city' => 'London'],
      ['lat' => 51.5134, 'lng' => -0.0890, 'city' => 'London'],
      ['lat' => 51.5014, 'lng' => -0.1419, 'city' => 'London'],
      ['lat' => 51.5263, 'lng' => -0.1031, 'city' => 'London'],
      ['lat' => 51.5080, 'lng' => -0.0760, 'city' => 'London'],
      ['lat' => 51.4975, 'lng' => -0.1357, 'city' => 'London'],
      ['lat' => 51.5145, 'lng' => -0.1522, 'city' => 'London'],
      ['lat' => 51.5310, 'lng' => -0.1050, 'city' => 'London'],
      ['lat' => 51.5058, 'lng' => -0.1610, 'city' => 'London'],
      ['lat' => 51.4890, 'lng' => -0.1150, 'city' => 'London'],
      ['lat' => 51.5200, 'lng' => -0.1710, 'city' => 'London'],
      ['lat' => 51.5350, 'lng' => -0.0890, 'city' => 'London'],
      ['lat' => 51.4940, 'lng' => -0.1760, 'city' => 'London'],
      ['lat' => 51.5410, 'lng' => -0.1430, 'city' => 'London'],
      ['lat' => 51.4850, 'lng' => -0.0980, 'city' => 'London'],
      ['lat' => 51.5170, 'lng' => -0.1080, 'city' => 'London'],
      ['lat' => 51.5020, 'lng' => -0.1560, 'city' => 'London'],
      ['lat' => 51.5280, 'lng' => -0.1340, 'city' => 'London'],

      // Birmingham (20)
      ['lat' => 52.4862, 'lng' => -1.8904, 'city' => 'Birmingham'],
      ['lat' => 52.4796, 'lng' => -1.9026, 'city' => 'Birmingham'],
      ['lat' => 52.4730, 'lng' => -1.8980, 'city' => 'Birmingham'],
      ['lat' => 52.4900, 'lng' => -1.8850, 'city' => 'Birmingham'],
      ['lat' => 52.4815, 'lng' => -1.8770, 'city' => 'Birmingham'],
      ['lat' => 52.4770, 'lng' => -1.9100, 'city' => 'Birmingham'],
      ['lat' => 52.4840, 'lng' => -1.8680, 'city' => 'Birmingham'],
      ['lat' => 52.4920, 'lng' => -1.8940, 'city' => 'Birmingham'],
      ['lat' => 52.4700, 'lng' => -1.8830, 'city' => 'Birmingham'],
      ['lat' => 52.4880, 'lng' => -1.9060, 'city' => 'Birmingham'],
      ['lat' => 52.4755, 'lng' => -1.8720, 'city' => 'Birmingham'],
      ['lat' => 52.4830, 'lng' => -1.9150, 'city' => 'Birmingham'],
      ['lat' => 52.4950, 'lng' => -1.8800, 'city' => 'Birmingham'],
      ['lat' => 52.4680, 'lng' => -1.8960, 'city' => 'Birmingham'],
      ['lat' => 52.4810, 'lng' => -1.8600, 'city' => 'Birmingham'],
      ['lat' => 52.4870, 'lng' => -1.9200, 'city' => 'Birmingham'],
      ['lat' => 52.4740, 'lng' => -1.8650, 'city' => 'Birmingham'],
      ['lat' => 52.4930, 'lng' => -1.9020, 'city' => 'Birmingham'],
      ['lat' => 52.4660, 'lng' => -1.8880, 'city' => 'Birmingham'],
      ['lat' => 52.4790, 'lng' => -1.8550, 'city' => 'Birmingham'],

      // Edinburgh (15)
      ['lat' => 55.9533, 'lng' => -3.1883, 'city' => 'Edinburgh'],
      ['lat' => 55.9500, 'lng' => -3.1950, 'city' => 'Edinburgh'],
      ['lat' => 55.9570, 'lng' => -3.1830, 'city' => 'Edinburgh'],
      ['lat' => 55.9480, 'lng' => -3.2000, 'city' => 'Edinburgh'],
      ['lat' => 55.9550, 'lng' => -3.1760, 'city' => 'Edinburgh'],
      ['lat' => 55.9610, 'lng' => -3.1900, 'city' => 'Edinburgh'],
      ['lat' => 55.9460, 'lng' => -3.1850, 'city' => 'Edinburgh'],
      ['lat' => 55.9590, 'lng' => -3.2050, 'city' => 'Edinburgh'],
      ['lat' => 55.9440, 'lng' => -3.1780, 'city' => 'Edinburgh'],
      ['lat' => 55.9520, 'lng' => -3.2100, 'city' => 'Edinburgh'],
      ['lat' => 55.9630, 'lng' => -3.1700, 'city' => 'Edinburgh'],
      ['lat' => 55.9470, 'lng' => -3.1650, 'city' => 'Edinburgh'],
      ['lat' => 55.9560, 'lng' => -3.2150, 'city' => 'Edinburgh'],
      ['lat' => 55.9420, 'lng' => -3.1920, 'city' => 'Edinburgh'],
      ['lat' => 55.9580, 'lng' => -3.1600, 'city' => 'Edinburgh'],

      // Cardiff (10)
      ['lat' => 51.4816, 'lng' => -3.1791, 'city' => 'Cardiff'],
      ['lat' => 51.4780, 'lng' => -3.1820, 'city' => 'Cardiff'],
      ['lat' => 51.4850, 'lng' => -3.1750, 'city' => 'Cardiff'],
      ['lat' => 51.4760, 'lng' => -3.1700, 'city' => 'Cardiff'],
      ['lat' => 51.4830, 'lng' => -3.1880, 'city' => 'Cardiff'],
      ['lat' => 51.4870, 'lng' => -3.1650, 'city' => 'Cardiff'],
      ['lat' => 51.4740, 'lng' => -3.1900, 'city' => 'Cardiff'],
      ['lat' => 51.4800, 'lng' => -3.1600, 'city' => 'Cardiff'],
      ['lat' => 51.4890, 'lng' => -3.1850, 'city' => 'Cardiff'],
      ['lat' => 51.4720, 'lng' => -3.1770, 'city' => 'Cardiff'],

      // Belfast (10)
      ['lat' => 54.5973, 'lng' => -5.9301, 'city' => 'Belfast'],
      ['lat' => 54.5950, 'lng' => -5.9350, 'city' => 'Belfast'],
      ['lat' => 54.6000, 'lng' => -5.9250, 'city' => 'Belfast'],
      ['lat' => 54.5930, 'lng' => -5.9400, 'city' => 'Belfast'],
      ['lat' => 54.6020, 'lng' => -5.9200, 'city' => 'Belfast'],
      ['lat' => 54.5910, 'lng' => -5.9320, 'city' => 'Belfast'],
      ['lat' => 54.6040, 'lng' => -5.9280, 'city' => 'Belfast'],
      ['lat' => 54.5890, 'lng' => -5.9380, 'city' => 'Belfast'],
      ['lat' => 54.6010, 'lng' => -5.9150, 'city' => 'Belfast'],
      ['lat' => 54.5960, 'lng' => -5.9430, 'city' => 'Belfast'],

      // Glasgow (10)
      ['lat' => 55.8642, 'lng' => -4.2518, 'city' => 'Glasgow'],
      ['lat' => 55.8610, 'lng' => -4.2550, 'city' => 'Glasgow'],
      ['lat' => 55.8670, 'lng' => -4.2480, 'city' => 'Glasgow'],
      ['lat' => 55.8590, 'lng' => -4.2600, 'city' => 'Glasgow'],
      ['lat' => 55.8700, 'lng' => -4.2450, 'city' => 'Glasgow'],
      ['lat' => 55.8560, 'lng' => -4.2530, 'city' => 'Glasgow'],
      ['lat' => 55.8630, 'lng' => -4.2400, 'city' => 'Glasgow'],
      ['lat' => 55.8720, 'lng' => -4.2580, 'city' => 'Glasgow'],
      ['lat' => 55.8580, 'lng' => -4.2650, 'city' => 'Glasgow'],
      ['lat' => 55.8660, 'lng' => -4.2350, 'city' => 'Glasgow'],

      // Leeds (10)
      ['lat' => 53.8008, 'lng' => -1.5491, 'city' => 'Leeds'],
      ['lat' => 53.7980, 'lng' => -1.5520, 'city' => 'Leeds'],
      ['lat' => 53.8030, 'lng' => -1.5460, 'city' => 'Leeds'],
      ['lat' => 53.7960, 'lng' => -1.5550, 'city' => 'Leeds'],
      ['lat' => 53.8050, 'lng' => -1.5430, 'city' => 'Leeds'],
      ['lat' => 53.7940, 'lng' => -1.5500, 'city' => 'Leeds'],
      ['lat' => 53.8010, 'lng' => -1.5380, 'city' => 'Leeds'],
      ['lat' => 53.7990, 'lng' => -1.5580, 'city' => 'Leeds'],
      ['lat' => 53.8060, 'lng' => -1.5410, 'city' => 'Leeds'],
      ['lat' => 53.7950, 'lng' => -1.5350, 'city' => 'Leeds'],

      // Liverpool (10)
      ['lat' => 53.4084, 'lng' => -2.9916, 'city' => 'Liverpool'],
      ['lat' => 53.4060, 'lng' => -2.9880, 'city' => 'Liverpool'],
      ['lat' => 53.4100, 'lng' => -2.9950, 'city' => 'Liverpool'],
      ['lat' => 53.4040, 'lng' => -2.9850, 'city' => 'Liverpool'],
      ['lat' => 53.4120, 'lng' => -2.9980, 'city' => 'Liverpool'],
      ['lat' => 53.4020, 'lng' => -2.9900, 'city' => 'Liverpool'],
      ['lat' => 53.4070, 'lng' => -2.9820, 'city' => 'Liverpool'],
      ['lat' => 53.4140, 'lng' => -2.9940, 'city' => 'Liverpool'],
      ['lat' => 53.4050, 'lng' => -2.9960, 'city' => 'Liverpool'],
      ['lat' => 53.4090, 'lng' => -2.9800, 'city' => 'Liverpool'],

      // Bristol (10)
      ['lat' => 51.4545, 'lng' => -2.5879, 'city' => 'Bristol'],
      ['lat' => 51.4520, 'lng' => -2.5910, 'city' => 'Bristol'],
      ['lat' => 51.4570, 'lng' => -2.5850, 'city' => 'Bristol'],
      ['lat' => 51.4500, 'lng' => -2.5940, 'city' => 'Bristol'],
      ['lat' => 51.4590, 'lng' => -2.5820, 'city' => 'Bristol'],
      ['lat' => 51.4480, 'lng' => -2.5900, 'city' => 'Bristol'],
      ['lat' => 51.4560, 'lng' => -2.5780, 'city' => 'Bristol'],
      ['lat' => 51.4530, 'lng' => -2.5960, 'city' => 'Bristol'],
      ['lat' => 51.4610, 'lng' => -2.5830, 'city' => 'Bristol'],
      ['lat' => 51.4490, 'lng' => -2.5870, 'city' => 'Bristol'],

      // Sheffield (10)
      ['lat' => 53.3811, 'lng' => -1.4701, 'city' => 'Sheffield'],
      ['lat' => 53.3790, 'lng' => -1.4730, 'city' => 'Sheffield'],
      ['lat' => 53.3830, 'lng' => -1.4670, 'city' => 'Sheffield'],
      ['lat' => 53.3770, 'lng' => -1.4760, 'city' => 'Sheffield'],
      ['lat' => 53.3850, 'lng' => -1.4640, 'city' => 'Sheffield'],
      ['lat' => 53.3750, 'lng' => -1.4710, 'city' => 'Sheffield'],
      ['lat' => 53.3800, 'lng' => -1.4600, 'city' => 'Sheffield'],
      ['lat' => 53.3840, 'lng' => -1.4780, 'city' => 'Sheffield'],
      ['lat' => 53.3760, 'lng' => -1.4650, 'city' => 'Sheffield'],
      ['lat' => 53.3820, 'lng' => -1.4800, 'city' => 'Sheffield'],

      // Newcastle (10)
      ['lat' => 54.9783, 'lng' => -1.6178, 'city' => 'Newcastle'],
      ['lat' => 54.9760, 'lng' => -1.6200, 'city' => 'Newcastle'],
      ['lat' => 54.9800, 'lng' => -1.6150, 'city' => 'Newcastle'],
      ['lat' => 54.9740, 'lng' => -1.6230, 'city' => 'Newcastle'],
      ['lat' => 54.9820, 'lng' => -1.6120, 'city' => 'Newcastle'],
      ['lat' => 54.9720, 'lng' => -1.6190, 'city' => 'Newcastle'],
      ['lat' => 54.9770, 'lng' => -1.6100, 'city' => 'Newcastle'],
      ['lat' => 54.9810, 'lng' => -1.6250, 'city' => 'Newcastle'],
      ['lat' => 54.9750, 'lng' => -1.6140, 'city' => 'Newcastle'],
      ['lat' => 54.9790, 'lng' => -1.6270, 'city' => 'Newcastle'],

      // Nottingham (10)
      ['lat' => 52.9548, 'lng' => -1.1581, 'city' => 'Nottingham'],
      ['lat' => 52.9520, 'lng' => -1.1610, 'city' => 'Nottingham'],
      ['lat' => 52.9570, 'lng' => -1.1550, 'city' => 'Nottingham'],
      ['lat' => 52.9500, 'lng' => -1.1640, 'city' => 'Nottingham'],
      ['lat' => 52.9590, 'lng' => -1.1520, 'city' => 'Nottingham'],
      ['lat' => 52.9480, 'lng' => -1.1600, 'city' => 'Nottingham'],
      ['lat' => 52.9560, 'lng' => -1.1490, 'city' => 'Nottingham'],
      ['lat' => 52.9530, 'lng' => -1.1660, 'city' => 'Nottingham'],
      ['lat' => 52.9580, 'lng' => -1.1530, 'city' => 'Nottingham'],
      ['lat' => 52.9510, 'lng' => -1.1570, 'city' => 'Nottingham'],
  ];
 $comments = [
     // Manchester (40)
     "Spotted chasing pigeons near Piccadilly Gardens",
     "Seen sniffing around bins on Deansgate",
     "Noticed running through St Peter's Square",
     "Wandering near the Arndale Centre entrance",
     "Seen resting under a bench in Sackville Gardens",
     "Spotted near the tram stop on Market Street",
     "Running across the car park at Manchester Piccadilly station",
     "Seen near the fountain at Exchange Square",
     "Noticed barking at ducks in Castlefield canal basin",
     "Spotted near the Beetham Tower",
     "Seen outside Primark on Market Street",
     "Wandering through the Northern Quarter alleys",
     "Spotted near the Refuge bar on Oxford Street",
     "Seen near the Palace Theatre entrance",
     "Running along the Bridgewater Canal towpath",
     "Noticed near the Hilton Hotel on Deansgate",
     "Spotted outside Victoria Baths",
     "Seen near the Manchester Arena entrance",
     "Wandering near Salford Central station",
     "Spotted near the Science and Industry Museum",
     "Seen near the old fire station on London Road",
     "Noticed near Whitworth Park gates",
     "Running through Platt Fields Park",
     "Spotted near Fallowfield Sainsbury's",
     "Seen outside the curry mile restaurants on Wilmslow Road",
     "Wandering near Owens Park student halls",
     "Spotted near the Apollo theatre in Ardwick",
     "Seen near Longsight Market",
     "Noticed near the Etihad Stadium car park",
     "Running along Stockport Road near Levenshulme",
     "Spotted near Old Trafford cricket ground",
     "Seen near Chorlton Water Park",
     "Noticed near Didsbury village shops",
     "Wandering along Fog Lane Park",
     "Spotted near Burnage Library",
     "Seen near Withington Tesco",
     "Running through Alexandra Park in Moss Side",
     "Spotted near the Trafford Centre bus stop",
     "Seen near MediaCityUK in Salford Quays",
     "Noticed outside Whalley Range chippy",

     // London (25)
     "Spotted near the gates of Hyde Park",
     "Seen running past Big Ben scaffolding",
     "Noticed near Covent Garden market stalls",
     "Wandering near King's Cross station entrance",
     "Seen near the Tower of London gift shop",
     "Spotted near Oxford Circus tube exit",
     "Running through Regent's Park near the boating lake",
     "Seen outside Camden Market food stalls",
     "Noticed near Tower Bridge walkway",
     "Spotted near Victoria station taxi rank",
     "Seen near the Southbank skatepark",
     "Wandering near Bermondsey antique market",
     "Spotted near Westminster Abbey gardens",
     "Seen outside Selfridges on Oxford Street",
     "Running near Angel Islington green",
     "Noticed near Kensington Palace gates",
     "Spotted near Elephant and Castle roundabout",
     "Seen near Paddington Basin canal",
     "Wandering near Shoreditch High Street overground",
     "Spotted near Battersea Power Station",
     "Seen near Hampstead Heath ponds",
     "Noticed near Brixton market entrance",
     "Running along the Barbican Estate walkways",
     "Spotted near Soho Square gardens",
     "Seen near London Bridge Borough Market",

     // Birmingham (20)
     "Spotted near the Bullring shopping centre",
     "Seen near New Street station concourse",
     "Noticed wandering near the Birmingham Library",
     "Running through Cannon Hill Park",
     "Spotted near the Jewellery Quarter clocktower",
     "Seen near Edgbaston cricket ground gates",
     "Wandering along the canal near Brindleyplace",
     "Noticed near the Mailbox complex",
     "Spotted near Selly Oak train station",
     "Seen near Aston University campus",
     "Running near the Custard Factory in Digbeth",
     "Spotted near Moseley village shops",
     "Seen near Handsworth Park",
     "Noticed near Small Heath retail park",
     "Wandering near Five Ways roundabout",
     "Spotted near Harborne High Street",
     "Seen near Kings Heath park entrance",
     "Running near Perry Barr greyhound track",
     "Noticed near Erdington High Street",
     "Spotted near Sutton Park visitor centre",

     // Edinburgh (15)
     "Spotted near the Royal Mile cobblestones",
     "Seen near Edinburgh Castle esplanade",
     "Noticed near Princes Street Gardens",
     "Wandering near Waverley station entrance",
     "Running through The Meadows park",
     "Spotted near Grassmarket pub benches",
     "Seen near Arthur's Seat car park",
     "Noticed near Stockbridge market area",
     "Spotted near Leith Walk shops",
     "Seen near Bruntsfield Links",
     "Wandering near Morningside Road cafes",
     "Running near Holyrood Palace gates",
     "Spotted near Tollcross junction",
     "Seen near Portobello beach promenade",
     "Noticed near Haymarket station",

     // Cardiff (10)
     "Spotted near Cardiff Castle walls",
     "Seen near the Principality Stadium gates",
     "Noticed near Bute Park riverside walk",
     "Wandering near Cardiff Bay barrage",
     "Running through Roath Park near the lake",
     "Spotted near St David's shopping centre",
     "Seen near Cardiff Queen Street station",
     "Noticed near Pontcanna Fields",
     "Spotted near Canton Library",
     "Seen near Splott Road shops",

     // Belfast (10)
     "Spotted near Belfast City Hall gardens",
     "Seen near the Cathedral Quarter cobbled streets",
     "Noticed near Titanic Quarter visitor centre",
     "Wandering near Botanic Gardens palm house",
     "Running near Queen's University main building",
     "Spotted near Victoria Square shopping centre",
     "Seen near Ormeau Park riverside",
     "Noticed near Falls Road murals",
     "Spotted near St George's Market",
     "Seen near Stormont Estate grounds",

     // Glasgow (10)
     "Spotted near George Square statues",
     "Seen near Buchanan Street shopping area",
     "Noticed near Glasgow Cathedral grounds",
     "Wandering near the Clyde riverside walkway",
     "Running through Kelvingrove Park",
     "Spotted near the Barras Market",
     "Seen near Byres Road in the West End",
     "Noticed near Glasgow Central station",
     "Spotted near the Hydro arena entrance",
     "Seen near Queen's Park in Southside",

     // Leeds (10)
     "Spotted near Leeds Corn Exchange",
     "Seen near Kirkgate Market stalls",
     "Noticed near the Leeds Arena",
     "Wandering near Roundhay Park lake",
     "Running along the Leeds-Liverpool canal",
     "Spotted near Headingley stadium",
     "Seen near Kirkstall Abbey ruins",
     "Noticed near Chapel Allerton shops",
     "Spotted near Meanwood Valley trail",
     "Seen near Horsforth village centre",

     // Liverpool (10)
     "Spotted near the Albert Dock waterfront",
     "Seen near Liverpool ONE shopping area",
     "Noticed near Anfield stadium gates",
     "Wandering near Sefton Park palm house",
     "Running along the Mersey riverfront",
     "Spotted near Bold Street cafes",
     "Seen near Lime Street station",
     "Noticed near Lark Lane restaurants",
     "Spotted near Penny Lane roundabout",
     "Seen near Toxteth Park cemetery",

     // Bristol (10)
     "Spotted near Clifton Suspension Bridge viewpoint",
     "Seen near Bristol Harbourside",
     "Noticed near Cabot Circus entrance",
     "Wandering near Castle Park riverside",
     "Running through St Andrews Park",
     "Spotted near Gloucester Road shops",
     "Seen near Temple Meads station",
     "Noticed near Stokes Croft street art",
     "Spotted near Bedminster Asda car park",
     "Seen near Southville cafe strip",

     // Sheffield (10)
     "Spotted near the Peace Gardens fountains",
     "Seen near Meadowhall shopping centre",
     "Noticed near Sheffield Cathedral",
     "Wandering near Endcliffe Park",
     "Running through Weston Park",
     "Spotted near Ecclesall Road restaurants",
     "Seen near Kelham Island quarter",
     "Noticed near Sharrow Vale Road shops",
     "Spotted near Sheffield station entrance",
     "Seen near Abbeydale Road",

     // Newcastle (10)
     "Spotted near the Tyne Bridge",
     "Seen near Grey's Monument",
     "Noticed near the Quayside market area",
     "Wandering near Jesmond Dene park",
     "Running through Leazes Park",
     "Spotted near Eldon Square shopping centre",
     "Seen near Ouseburn Valley",
     "Noticed near Heaton Park",
     "Spotted near Newcastle Central station",
     "Seen near Gosforth High Street",

     // Nottingham (10)
     "Spotted near Nottingham Castle grounds",
     "Seen near the Old Market Square",
     "Noticed near the Lace Market quarter",
     "Wandering near Wollaton Hall deer park",
     "Running through the Arboretum",
     "Spotted near Hockley village",
     "Seen near Beeston High Road",
     "Noticed near Sneinton Market",
     "Spotted near Trent Bridge cricket ground",
     "Seen near Sherwood shops",
 ];
 // Cache directory for geocode responses
 $cacheDir = __DIR__ . '/../cache/geocode/';
 if (!is_dir($cacheDir)) {
     mkdir($cacheDir, 0755, true);
 }

 // Reverse geocode with caching
 function getAddress($lat, $lng, $cacheDir) {
     $cacheFile = $cacheDir . round($lat, 4) . '_' . round($lng, 4) . '.json';

     if (file_exists($cacheFile)) {
         $response = file_get_contents($cacheFile);
     } else {
         $url = 'https://nominatim.openstreetmap.org/reverse?format=json'
             . '&lat=' . urlencode($lat)
             . '&lon=' . urlencode($lng);

         $options = ['http' => ['header' => "User-Agent: PetWatch/1.0\r\n"]];
         $context = stream_context_create($options);
         $response = file_get_contents($url, false, $context);

         if ($response === false) {
             return "$lat, $lng";
         }

         file_put_contents($cacheFile, $response);
         // Nominatim rate limit: 1 request per second
         sleep(1);
     }

     $data = json_decode($response, true);
     $address = $data['address'] ?? [];
     $parts = [];

     if (!empty($address['road'])) $parts[] = $address['road'];
     elseif (!empty($address['neighbourhood'])) $parts[] = $address['neighbourhood'];

     if (!empty($address['city'])) $parts[] = $address['city'];
     elseif (!empty($address['town'])) $parts[] = $address['town'];
     elseif (!empty($address['village'])) $parts[] = $address['village'];

     if (!empty($address['postcode'])) $parts[] = $address['postcode'];

     return implode(', ', $parts) ?: ($data['display_name'] ?? "$lat, $lng");
 }

 // Insert sightings and locations
 $sightingStmt = $db->prepare(
     "INSERT INTO sightings (pet_id, user_id, comment) VALUES (:pet_id, :user_id, :comment)"
 );
 $locationStmt = $db->prepare(
     "INSERT INTO locations (pet_id, latitude, longitude, timestamp, address) VALUES (:pet_id, :lat, :lng, :timestamp, :address)"
 );

 for ($i = 0; $i < 200; $i++) {
     $petId = $i + 1;
     $userId = $i + 1;
     $loc = $locations[$i];
     $comment = $comments[$i % count($comments)];
     $timestamp = date('Y-m-d H:i:s', strtotime("-" . rand(1, 90) . " days"));

     echo "Pet $petId: Geocoding {$loc['lat']}, {$loc['lng']} ({$loc['city']})... ";
     $address = getAddress($loc['lat'], $loc['lng'], $cacheDir);
     echo "$address\n";

     $sightingStmt->execute([
         ':pet_id' => $petId,
         ':user_id' => $userId,
         ':comment' => $comment
     ]);

     $locationStmt->execute([
         ':pet_id' => $petId,
         ':lat' => $loc['lat'],
         ':lng' => $loc['lng'],
         ':timestamp' => $timestamp,
         ':address' => $address
     ]);
 }

 echo "\nDone. 200 sightings and locations inserted.\n";
