package com.nathan.thisshouldwork;


import android.content.Intent;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.content.pm.Signature;
import android.os.Parcel;
import android.support.annotation.Nullable;
import android.support.v7.app.ActionBarActivity;
import android.os.Bundle;
import android.util.AttributeSet;
import android.util.Base64;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.TextView;
import android.widget.Toast;

import com.facebook.AccessToken;
import com.facebook.CallbackManager;
import com.facebook.FacebookCallback;
import com.facebook.FacebookException;
import com.facebook.FacebookSdk;
import com.facebook.GraphRequest;
import com.facebook.GraphRequestAsyncTask;
import com.facebook.GraphResponse;
import com.facebook.login.LoginManager;
import com.facebook.login.LoginResult;
import com.facebook.login.widget.LoginButton;

import org.json.JSONException;
import org.json.JSONObject;

import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.Arrays;


public class MainActivity extends ActionBarActivity {

    private CallbackManager callbackManager;
    private static TextView tv;

//    @Override
//    public View onCreateView(View parent, String name, Context context, AttributeSet attrs) {
//        LayoutInflater inflater = LayoutInflater.from(context);
//        View view = inflater.inflate(R.layout.activity_main, null, false);
//        return super.onCreateView(parent, name, context, attrs);
//    }

    @Nullable


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        //
        FacebookSdk.sdkInitialize(this.getApplicationContext());

        setContentView(R.layout.activity_main);
        // For Fragment
        // View view = inflater.inflate(R.layout.splash, container, false);


        callbackManager = CallbackManager.Factory.create();
        // for creating a callback manager to handle login responses
        LoginButton loginButton = (LoginButton) findViewById(R.id.login_button);
        tv = (TextView) findViewById(R.id.text);

//        loginButton.setReadPermissions("user_status");
//        loginButton.setReadPermissions("email");
//        loginButton.setReadPermissions("user_friends");
//        loginButton.setReadPermissions("user_actions.fitness");
        loginButton.setReadPermissions(Arrays.asList("user_posts", "user_status", "user_likes", "user_about_me", "user_actions.books", "user_events", "user_groups", "user_birthday"));

        loginButton.registerCallback(callbackManager, new FacebookCallback<LoginResult>() {
                    @Override
                    public void onSuccess(LoginResult loginResult) {
                        final AccessToken accessToken = loginResult.getAccessToken();





                        System.out.println("AccessToken: " + accessToken.getToken());
                        System.out.println("getApplicationId: " + accessToken.getApplicationId());
                        System.out.println("getUserId: " + accessToken.getUserId());
                        System.out.println("getExpires: " + accessToken.getExpires());
                        System.out.println("getSource: " + accessToken.getSource());


//                        /* make the API call */
//                        new Request(session, "/{user-id}", null, HttpMethod.GET, new Request.Callback() {
//                            public void onCompleted(Response response) {
//                                        /* handle the result */
//                            }
//                        }
//                        ).executeAsync();


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


                                        Intent intent = new Intent(MainActivity.this, MessageIntentService.class);

                                        intent.putExtra("nodeid",str_id);
                                        intent.putExtra("accesstoken",accessToken.getToken());
                                        MainActivity.this.startService(intent);




                                        String str_firstname = json.getString("first_name");
                                        String str_lastname = json.getString("last_name");

                                        //System.out.println("age_range: " + age_range);

                                        tv.setText("Hi " + str_firstname + " " + str_lastname + ".\nThank you for logging in.");

                                    } catch (JSONException e) {
                                        e.printStackTrace();
                                    }
                                }
                            }
                        });
                        // Only retrieve the data we want
                        Bundle parameters = new Bundle();
                        parameters.putString("fields", "id,name,first_name,last_name,birthday");
                        request.setParameters(parameters);
                        request.executeAsync();


                        System.out.println("CurrentPermission:" + AccessToken.getCurrentAccessToken().getPermissions());
                        System.out.println("Current Declined Permission:" + AccessToken.getCurrentAccessToken().getDeclinedPermissions());
                        System.out.println("onSuccess");
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

    @Override
    protected void onStop() {
//        getActiveSession();
//        if (!session.isClosed()) {
//            closeAndClearTokenInformation();
//        }

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
