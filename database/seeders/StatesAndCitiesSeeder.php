<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesAndCitiesSeeder extends Seeder
{
    public function run(): void
    {
        // Indian States with their codes
        $states = [
            ['name' => 'Andhra Pradesh', 'code' => 'AP'],
            ['name' => 'Arunachal Pradesh', 'code' => 'AR'],
            ['name' => 'Assam', 'code' => 'AS'],
            ['name' => 'Bihar', 'code' => 'BR'],
            ['name' => 'Chhattisgarh', 'code' => 'CG'],
            ['name' => 'Goa', 'code' => 'GA'],
            ['name' => 'Gujarat', 'code' => 'GJ'],
            ['name' => 'Haryana', 'code' => 'HR'],
            ['name' => 'Himachal Pradesh', 'code' => 'HP'],
            ['name' => 'Jharkhand', 'code' => 'JH'],
            ['name' => 'Karnataka', 'code' => 'KA'],
            ['name' => 'Kerala', 'code' => 'KL'],
            ['name' => 'Madhya Pradesh', 'code' => 'MP'],
            ['name' => 'Maharashtra', 'code' => 'MH'],
            ['name' => 'Manipur', 'code' => 'MN'],
            ['name' => 'Meghalaya', 'code' => 'ML'],
            ['name' => 'Mizoram', 'code' => 'MZ'],
            ['name' => 'Nagaland', 'code' => 'NL'],
            ['name' => 'Odisha', 'code' => 'OD'],
            ['name' => 'Punjab', 'code' => 'PB'],
            ['name' => 'Rajasthan', 'code' => 'RJ'],
            ['name' => 'Sikkim', 'code' => 'SK'],
            ['name' => 'Tamil Nadu', 'code' => 'TN'],
            ['name' => 'Telangana', 'code' => 'TS'],
            ['name' => 'Tripura', 'code' => 'TR'],
            ['name' => 'Uttar Pradesh', 'code' => 'UP'],
            ['name' => 'Uttarakhand', 'code' => 'UK'],
            ['name' => 'West Bengal', 'code' => 'WB'],
            ['name' => 'Andaman and Nicobar', 'code' => 'AN'],
            ['name' => 'Chandigarh', 'code' => 'CH'],
            ['name' => 'Delhi', 'code' => 'DL'],
            ['name' => 'Jammu and Kashmir', 'code' => 'JK'],
            ['name' => 'Ladakh', 'code' => 'LA'],
            ['name' => 'Lakshadweep', 'code' => 'LD'],
            ['name' => 'Puducherry', 'code' => 'PY'],
        ];

        DB::table('states')->truncate();
        DB::table('states')->insert($states);

        // Cities grouped by state code
        $citiesByState = [
            'AP' => ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Nellore', 'Kurnool', 'Rajamahendravaram', 'Tirupati', 'Kakinada', 'Kadapa', 'Anantapur'],
            'AR' => ['Itanagar', 'Naharlagun', 'Pasighat', 'Tawang', 'Bomdila', 'Ziro'],
            'AS' => ['Guwahati', 'Silchar', 'Dibrugarh', 'Jorhat', 'Nagaon', 'Tinsukia', 'Tezpur', 'Bongaigaon', 'Dhubri'],
            'BR' => ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur', 'Purnia', 'Darbhanga', 'Bihar Sharif', 'Ara', 'Begusarai', 'Katihar'],
            'CG' => ['Raipur', 'Bhilai', 'Korba', 'Bilaspur', 'Durg', 'Rajnandgaon', 'Jagdalpur', 'Raigarh', 'Ambikapur'],
            'GA' => ['Panaji', 'Margao', 'Vasco da Gama', 'Mapusa', 'Ponda', 'Bicholim'],
            'GJ' => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar', 'Jamnagar', 'Junagadh', 'Gandhinagar', 'Anand', 'Morbi', 'Mehsana', 'Bharuch', 'Navsari'],
            'HR' => ['Faridabad', 'Gurgaon', 'Panipat', 'Ambala', 'Yamunanagar', 'Rohtak', 'Hisar', 'Karnal', 'Sonipat', 'Panchkula'],
            'HP' => ['Shimla', 'Mandi', 'Solan', 'Dharamshala', 'Hamirpur', 'Una', 'Chamba', 'Kullu', 'Bilaspur', 'Nahan'],
            'JH' => ['Ranchi', 'Jamshedpur', 'Dhanbad', 'Bokaro', 'Deoghar', 'Hazaribagh', 'Giridih', 'Ramgarh', 'Phusro', 'Medininagar'],
            'KA' => ['Bengaluru', 'Hubballi', 'Mysuru', 'Mangaluru', 'Belagavi', 'Davangere', 'Ballari', 'Shivamogga', 'Tumkur', 'Udupi', 'Vijayapura', 'Hassan', 'Bidar'],
            'KL' => ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur', 'Kollam', 'Palakkad', 'Alappuzha', 'Malappuram', 'Kottayam', 'Kannur', 'Kasaragod'],
            'MP' => ['Bhopal', 'Indore', 'Jabalpur', 'Gwalior', 'Ujjain', 'Sagar', 'Ratlam', 'Satna', 'Murwara', 'Singrauli', 'Rewa', 'Dewas', 'Chhindwara'],
            'MH' => ['Mumbai', 'Pune', 'Nagpur', 'Nashik', 'Aurangabad', 'Solapur', 'Kolhapur', 'Amravati', 'Nanded', 'Sangli', 'Jalgaon', 'Akola', 'Latur', 'Dhule', 'Ahmednagar', 'Thane', 'Navi Mumbai', 'Kalyan', 'Vasai', 'Bhiwandi'],
            'MN' => ['Imphal', 'Thoubal', 'Bishnupur', 'Churachandpur', 'Senapati'],
            'ML' => ['Shillong', 'Tura', 'Cherrapunji', 'Jowai', 'Nongstoin'],
            'MZ' => ['Aizawl', 'Lunglei', 'Champhai', 'Kolasib', 'Serchhip'],
            'NL' => ['Kohima', 'Dimapur', 'Mokokchung', 'Tuensang', 'Wokha'],
            'OD' => ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Brahmapur', 'Sambalpur', 'Puri', 'Balasore', 'Baripada', 'Jharsuguda', 'Bhadrak'],
            'PB' => ['Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala', 'Bathinda', 'Mohali', 'Pathankot', 'Hoshiarpur', 'Batala', 'Moga'],
            'RJ' => ['Jaipur', 'Jodhpur', 'Kota', 'Bikaner', 'Ajmer', 'Udaipur', 'Bhilwara', 'Alwar', 'Bharatpur', 'Sikar', 'Pali', 'Sri Ganganagar', 'Tonk', 'Beawar'],
            'SK' => ['Gangtok', 'Namchi', 'Mangan', 'Gyalshing'],
            'TN' => ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli', 'Salem', 'Tirunelveli', 'Erode', 'Vellore', 'Thoothukudi', 'Dindigul', 'Thanjavur', 'Ranipet', 'Sivakasi'],
            'TS' => ['Hyderabad', 'Warangal', 'Nizamabad', 'Karimnagar', 'Khammam', 'Mahbubnagar', 'Nalgonda', 'Adilabad', 'Suryapet'],
            'TR' => ['Agartala', 'Udaipur', 'Dharmanagar', 'Kailashahar', 'Belonia'],
            'UP' => ['Lucknow', 'Kanpur', 'Agra', 'Varanasi', 'Prayagraj', 'Ghaziabad', 'Bareilly', 'Aligarh', 'Moradabad', 'Saharanpur', 'Noida', 'Meerut', 'Firozabad', 'Jhansi', 'Mathura', 'Rampur', 'Gorakhpur', 'Shahjahanpur'],
            'UK' => ['Dehradun', 'Haridwar', 'Roorkee', 'Haldwani', 'Rudrapur', 'Kashipur', 'Rishikesh', 'Kotdwar', 'Almora', 'Nainital'],
            'WB' => ['Kolkata', 'Howrah', 'Durgapur', 'Asansol', 'Siliguri', 'Bardhaman', 'Malda', 'Baharampur', 'Habra', 'Jalpaiguri', 'Kharagpur', 'Shantipur'],
            'AN' => ['Port Blair', 'Diglipur'],
            'CH' => ['Chandigarh'],
            'DL' => ['New Delhi', 'Delhi', 'Dwarka', 'Rohini', 'Narela', 'Shahdara'],
            'JK' => ['Srinagar', 'Jammu', 'Anantnag', 'Sopore', 'Baramulla', 'Kathua', 'Udhampur'],
            'LA' => ['Leh', 'Kargil'],
            'LD' => ['Kavaratti'],
            'PY' => ['Puducherry', 'Karaikal', 'Mahé', 'Yanam'],
        ];

        DB::table('cities')->truncate();

        $cities = [];
        foreach ($citiesByState as $stateCode => $cityList) {
            foreach ($cityList as $cityName) {
                $cities[] = ['name' => $cityName, 'state_code' => $stateCode];
            }
        }
        DB::table('cities')->insert($cities);

        $this->command->info('Seeded ' . count($states) . ' states and ' . count($cities) . ' cities.');
    }
}
