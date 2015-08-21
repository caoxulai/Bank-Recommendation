package com.novramedia.dataminer;


import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothDevice;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.SharedPreferences;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.content.pm.Signature;
import android.support.annotation.Nullable;
import android.support.v7.app.ActionBarActivity;
import android.os.Bundle;
import android.util.Base64;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import com.facebook.AccessToken;
import com.facebook.CallbackManager;
import com.facebook.FacebookCallback;
import com.facebook.FacebookException;
import com.facebook.FacebookSdk;
import com.facebook.GraphRequest;
import com.facebook.GraphResponse;
import com.facebook.login.LoginManager;
import com.facebook.login.LoginResult;
import com.facebook.login.widget.LoginButton;

import org.json.JSONException;
import org.json.JSONObject;

import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.text.DecimalFormat;
import java.util.Arrays;

public class MainActivity extends ActionBarActivity {

    //For Facebook Login
    private CallbackManager callbackManager;
    private static TextView login_text;

    //For iBeacon Detection
    private static final String TAG = "MyActivity";
    private static final int REQUEST_ENABLE_BT = 1;
    public static final int TX_POWER = -58;
    private static TextView device_info;
    private static Button onBtn;
    private static TextView offBtn;
    private BluetoothAdapter myBluetoothAdapter;

    // Declare SharedPreferences
    public static final String MyPREFERENCES = "facebook_node";
    SharedPreferences sharedpreferences;

    // Declare receiver and specify action
    final BroadcastReceiver bReceiver = new BroadcastReceiver() {
        public void onReceive(Context context, Intent intent) {
//            Log.d(TAG, ">>>>OnReceive started");
            String action = intent.getAction();
            if (BluetoothDevice.ACTION_FOUND.equals(action)) {
                Log.d(TAG, ">>>>ACTION_FOUND started");
                BluetoothDevice device = intent.getParcelableExtra(BluetoothDevice.EXTRA_DEVICE);
                Log.d(TAG, "Device found: " + device.getName() + " MAC: " + device.getAddress());
                if ("BlueBeacon".equalsIgnoreCase(device.getName())) {
                    Log.d(TAG, "++This is our device!!!");
                    int rssi = intent.getShortExtra(BluetoothDevice.EXTRA_RSSI, Short.MIN_VALUE);
                    double dst = calculateDistance(TX_POWER, rssi);
                    dst = roundTo2Decimals(dst);
                    if (dst <= 3) {
                        device_info.setText("FREEZE!\n\n" + device.getAddress() + " \nSignal: " + rssi + " dBm\nDistance: " + roundTo2Decimals(dst) + " meters\n\n");


                        // Start BeaconIntentService
                        Intent beacon_intent = new Intent(MainActivity.this, BeaconIntentService.class);
                        beacon_intent.putExtra("beacon_address", device.getAddress());
                        sharedpreferences = getSharedPreferences(MyPREFERENCES, Context.MODE_PRIVATE);
                        // Extract the Facebook node ID of last login
                        String sp_node_id = sharedpreferences.getString("node_id", "----");
                        beacon_intent.putExtra("nodeid", sp_node_id);
                        MainActivity.this.startService(beacon_intent);

                        off();


                    } else {
                        device_info.setText(device.getAddress() + " \nSignal: " + rssi + " dBm\nDistance: " + roundTo2Decimals(dst) + " meters");
                    }

                } else if (!"BlueBeacon".equalsIgnoreCase(device.getName())) {
                    Log.d(TAG, "--This is not our device");
                }
//                Log.d(TAG, "<<<<<ACTION_FOUND finished");
            }
//            Log.d(TAG, "<<<<<OnReceive finished");
            else if (BluetoothAdapter.ACTION_DISCOVERY_FINISHED.equals(action)) {
                Log.d(TAG, "***********Entered the ACTION_DISCOVERY_FINISHED********");
                myBluetoothAdapter.startDiscovery();
            }
        }
    };


    @Nullable
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        Log.d(TAG, ">>!>>OnCreate started");
        super.onCreate(savedInstanceState);

        FacebookSdk.sdkInitialize(this.getApplicationContext());

        setContentView(R.layout.activity_main);

        callbackManager = CallbackManager.Factory.create();
        // for creating a callback manager to handle login responses
        LoginButton loginButton = (LoginButton) findViewById(R.id.login_button);
        login_text = (TextView) findViewById(R.id.login_text);
        device_info = (TextView) findViewById(R.id.device_text);

        loginButton.setReadPermissions(Arrays.asList("user_likes", "user_groups"));

        loginButton.registerCallback(callbackManager, new FacebookCallback<LoginResult>() {
                    @Override
                    public void onSuccess(LoginResult loginResult) {
                        final AccessToken accessToken = loginResult.getAccessToken();

//                        System.out.println("AccessToken: " + accessToken.getToken());
//                        System.out.println("getApplicationId: " + accessToken.getApplicationId());
//                        System.out.println("getUserId: " + accessToken.getUserId());
//                        System.out.println("getExpires: " + accessToken.getExpires());
//                        System.out.println("getSource: " + accessToken.getSource());

                        //GraphRequestAsyncTask request =
                        GraphRequest request = GraphRequest.newMeRequest(accessToken, new GraphRequest.GraphJSONObjectCallback() {
                            @Override
                            public void onCompleted(JSONObject json, GraphResponse response) {
                                if (response.getError() != null) {
                                    // handle error
                                    System.out.println("ERROR");
                                } else {
                                    System.out.println("Success");
                                    try {

                                        String jsonresult = String.valueOf(json);
                                        System.out.println("JSON Result" + jsonresult);

                                        //String age_range = json.getString("age_range");
                                        // important

                                        String str_id = json.getString("id");

                                        // store this user's node id for future use.
                                        sharedpreferences = getSharedPreferences(MyPREFERENCES, Context.MODE_PRIVATE);
                                        SharedPreferences.Editor editor = sharedpreferences.edit();
                                        editor.putString("node_id", str_id);
                                        editor.commit();


                                        // Start MessageIntentService
                                        Intent intent = new Intent(MainActivity.this, MessageIntentService.class);
                                        intent.putExtra("nodeid", str_id);
                                        intent.putExtra("accesstoken", accessToken.getToken());
                                        MainActivity.this.startService(intent);


                                        String str_firstname = json.getString("first_name");
                                        String str_lastname = json.getString("last_name");

                                        //System.out.println("age_range: " + age_range);

                                        //Extract node id info from SharedPreference
                                        String sp_node_id = sharedpreferences.getString("node_id", "----");
                                        login_text.setText("Hi " + str_firstname + " " + str_lastname + ".\nNode ID: " + sp_node_id + "\nThank you for logging in.");

                                    } catch (JSONException e) {
                                        e.printStackTrace();
                                    }
                                }
                            }
                        });
                        // Execute the quest defined above; Only retrieve the data we want
                        Bundle parameters = new Bundle();
                        parameters.putString("fields", "id,name,first_name,last_name");
                        request.setParameters(parameters);
                        request.executeAsync();


//                        System.out.println("CurrentPermission:" + AccessToken.getCurrentAccessToken().getPermissions());
//                        System.out.println("Current Declined Permission:" + AccessToken.getCurrentAccessToken().getDeclinedPermissions());
//                        System.out.println("onSuccess");
                        Toast.makeText(getApplicationContext(), "You have logged in. Thanks.", Toast.LENGTH_SHORT).show();
                    }

                    @Override
                    public void onCancel() {
                        // App code
                        System.out.println("onCancel");
                        Toast.makeText(getApplicationContext(), "Login Canceled", Toast.LENGTH_SHORT).show();
                    }

                    @Override
                    public void onError(FacebookException exception) {
                        // App code
                        System.out.println("onError");
                        Toast.makeText(getApplicationContext(), "Login Error", Toast.LENGTH_SHORT).show();
                    }
                }

        );


        // take an instance of BluetoothAdapter - Bluetooth radio
        myBluetoothAdapter = BluetoothAdapter.getDefaultAdapter();
        //***************To be modified******************
//        BTArrayAdapter = new ArrayAdapter<String>(this, android.R.layout.simple_list_item_1);

        if (myBluetoothAdapter == null) {
            onBtn.setEnabled(false);
            offBtn.setEnabled(false);

            Toast.makeText(getApplicationContext(), "Your device does not support Bluetooth",
                    Toast.LENGTH_LONG).show();
        } else {

            onBtn = (Button) findViewById(R.id.turnOn);
            onBtn.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    on();
                }
            });

            offBtn = (Button) findViewById(R.id.turnOff);
            offBtn.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {
                    off();
                }
            });

        }
        Log.d(TAG, ">>!>>OnCreate finished");


        // Add code to print out the key hash
        PackageInfo info;
        try {
            info = getPackageManager().getPackageInfo("com.nathan.thisshouldwork", PackageManager.GET_SIGNATURES);
            for (Signature signature : info.signatures) {
                MessageDigest md;
                md = MessageDigest.getInstance("SHA");
                md.update(signature.toByteArray());
                String something = new String(Base64.encode(md.digest(), 0));
                //String something = new String(Base64.encodeBytes(md.digest()));
                Log.e("hash key", something);
            }
        } catch (PackageManager.NameNotFoundException e1) {
            Log.e("name not found", e1.toString());
        } catch (NoSuchAlgorithmException e) {
            Log.e("no such an algorithm", e.toString());
        } catch (Exception e) {
            Log.e("exception", e.toString());
        }
    }

    public void on() {
        if (!myBluetoothAdapter.isEnabled()) {
            Intent turnOnIntent = new Intent(BluetoothAdapter.ACTION_REQUEST_ENABLE);
            startActivityForResult(turnOnIntent, REQUEST_ENABLE_BT);

            Toast.makeText(getApplicationContext(), "Bluetooth turned on & Detecting",
                    Toast.LENGTH_LONG).show();
        } else {
            Toast.makeText(getApplicationContext(), "Bluetooth is already on & Detecting",
                    Toast.LENGTH_LONG).show();
        }
        // Detect Beacons
        Log.d(TAG, ">>!>>Device thread started");

        device_info.setText("Detecting device");
        if (myBluetoothAdapter.isDiscovering()) {
            Log.d(TAG, ">>!>>Stopping discovering");
            myBluetoothAdapter.cancelDiscovery();
            Log.d(TAG, ">>!>>Starting discovering");
            myBluetoothAdapter.startDiscovery();
            registerReceiver(bReceiver, new IntentFilter(BluetoothDevice.ACTION_FOUND));
            IntentFilter intentFilter = new IntentFilter(BluetoothAdapter.ACTION_DISCOVERY_FINISHED);
            registerReceiver(bReceiver, intentFilter);
        } else {
            Log.d(TAG, ">>!>>Starting discovering");
            myBluetoothAdapter.startDiscovery();
            registerReceiver(bReceiver, new IntentFilter(BluetoothDevice.ACTION_FOUND));
            IntentFilter intentFilter = new IntentFilter(BluetoothAdapter.ACTION_DISCOVERY_FINISHED);
            registerReceiver(bReceiver, intentFilter);
        }
        Log.d(TAG, ">>!>>Device thread finished");
    }

    public void on_R() {
        if (!myBluetoothAdapter.isEnabled()) {
            Toast.makeText(getApplicationContext(), "Please turn on Bluetooth",
                    Toast.LENGTH_LONG).show();
        } else {
            Toast.makeText(getApplicationContext(), "Bluetooth is already on & Detecting",
                    Toast.LENGTH_LONG).show();
            // Detect Beacons
            Log.d(TAG, ">>!>>Device thread started");

            device_info.setText("Detecting device");
            if (myBluetoothAdapter.isDiscovering()) {
                Log.d(TAG, ">>!>>Stopping discovering");
                myBluetoothAdapter.cancelDiscovery();
                Log.d(TAG, ">>!>>Starting discovering");
                myBluetoothAdapter.startDiscovery();
                registerReceiver(bReceiver, new IntentFilter(BluetoothDevice.ACTION_FOUND));
                IntentFilter intentFilter = new IntentFilter(BluetoothAdapter.ACTION_DISCOVERY_FINISHED);
                registerReceiver(bReceiver, intentFilter);
            } else {
                Log.d(TAG, ">>!>>Starting discovering");
                myBluetoothAdapter.startDiscovery();
                registerReceiver(bReceiver, new IntentFilter(BluetoothDevice.ACTION_FOUND));
                IntentFilter intentFilter = new IntentFilter(BluetoothAdapter.ACTION_DISCOVERY_FINISHED);
                registerReceiver(bReceiver, intentFilter);
            }
            Log.d(TAG, ">>!>>Device thread finished");
        }

    }

    public void off() {
        myBluetoothAdapter.disable();

        Toast.makeText(getApplicationContext(), "Bluetooth turned off",
                Toast.LENGTH_LONG).show();
    }


    // two functions for distance calculation
    protected static double calculateDistance(int txPower, double rssi) {
        if (rssi == 0) {
            return -1.0; // if we cannot determine accuracy, return -1.
        }

        double ratio = rssi * 1.0 / txPower;
        if (ratio < 1.0) {
            return Math.pow(ratio, 10);
        } else {
            double accuracy = (0.89976) * Math.pow(ratio, 7.7095) + 0.111;
            return accuracy;
        }
    }

    private static double roundTo2Decimals(double val) {
        DecimalFormat df2 = new DecimalFormat("###.##");
        return Double.valueOf(df2.format(val));
    }


    @Override
    protected void onResume() {
        super.onResume();
        Log.d(TAG, ">>!>>OnResume started");
        on_R();
        Log.d(TAG, ">>!>>OnResume finished");
    }


    @Override
    protected void onDestroy() {
        Log.d(TAG, ">>!>>OnDestroy started");
        super.onDestroy();
        unregisterReceiver(bReceiver);
        Log.d(TAG, ">>!>>OnDestroy finished");
    }

    @Override
    protected void onStop() {
        LoginManager.getInstance().logOut();
        super.onStop();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        callbackManager.onActivityResult(requestCode, resultCode, data);
    }


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_main, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        int id = item.getItemId();

        //noinspection SimplifiableIfStatement
        if (id == R.id.action_settings) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }
}
