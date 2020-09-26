module Auth.Register exposing (..)

import Auth.Layout exposing (authButtonClasses, authContainer, authErrors, authInput, authLink)
import Html exposing (Html, button, div, form, text)
import Html.Attributes exposing (attribute, class, type_)
import Html.Events exposing (onSubmit)
import Json.Decode exposing (Decoder, Error, Value, decodeValue, dict, list, string)
import Json.Decode.Pipeline exposing (required)
import Json.Encode
import LaravelElm exposing (Errors, Page, page)
import Routes exposing (get, post)


type alias Props =
    { errors : Errors }


type alias State =
    { name : String
    , email : String
    , password : String
    , password_confirmation : String
    }


type alias Model =
    { props : Props
    , state : State
    }


type Msg
    = NewProps Value
    | Submit
    | SetName String
    | SetEmail String
    | SetPassword String
    | SetPasswordConfirmation String
    | Login


decodeProps : Decoder Props
decodeProps =
    Json.Decode.succeed Props
        |> required "errors" (dict (list string))


stateFromProps : Props -> State
stateFromProps props =
    { name = "", email = "", password = "", password_confirmation = "" }


main : Page Model Msg
main =
    page
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , view = view
        , update = update
        , newPropsMsg = NewProps
        }


update : Msg -> Model -> ( Model, Cmd Msg )
update msg { props, state } =
    case msg of
        NewProps newProps ->
            ( { props = Result.withDefault props (decodeValue decodeProps newProps)
              , state = state
              }
            , Cmd.none
            )

        SetName newName ->
            ( { props = props, state = { state | name = newName } }, Cmd.none )

        SetEmail newEmail ->
            ( { props = props, state = { state | email = newEmail } }, Cmd.none )

        SetPassword newPassword ->
            ( { props = props, state = { state | password = newPassword } }, Cmd.none )

        SetPasswordConfirmation newPasswordConfirmation ->
            ( { props = props, state = { state | password_confirmation = newPasswordConfirmation } }, Cmd.none )

        Login ->
            ( { props = props, state = state }, get Routes.login )

        Submit ->
            ( { props = props, state = state }
            , post <|
                Json.Encode.object
                    [ ( "url", Json.Encode.string Routes.register )
                    , ( "data"
                      , Json.Encode.object
                            [ ( "name", Json.Encode.string state.name )
                            , ( "email", Json.Encode.string state.email )
                            , ( "password", Json.Encode.string state.password )
                            , ( "password_confirmation", Json.Encode.string state.password_confirmation )
                            ]
                      )
                    ]
            )


view : Model -> Html Msg
view { props, state } =
    div []
        [ authContainer "Register"
            [ form [ onSubmit <| Submit ]
                [ div [ class "flex flex-wrap mb-6" ]
                    [ authInput SetName state.name props.errors "Name" "name" [ attribute "autofocus" "", attribute "required" "", attribute "autocomplete" "name" ]
                    , authErrors props.errors "name"
                    ]
                , div [ class "flex flex-wrap mb-6" ]
                    [ authInput SetEmail state.email props.errors "Email" "email" [ attribute "required" "", type_ "email", attribute "autocomplete" "email" ]
                    , authErrors props.errors "email"
                    ]
                , div [ class "flex flex-wrap mb-6" ]
                    [ authInput SetPassword state.password props.errors "Password" "password" [ attribute "required" "", type_ "password", attribute "autocomplete" "password" ]
                    , authErrors props.errors "password"
                    ]
                , div [ class "flex flex-wrap mb-6" ]
                    [ authInput SetPasswordConfirmation state.password_confirmation props.errors "Confirm Password" "password_confirmation" [ attribute "required" "", type_ "password" ]
                    , authErrors props.errors "password_confirmation"
                    ]
                , div [ class "flex flex-wrap items-center" ]
                    [ button [ class authButtonClasses, type_ "submit" ]
                        [ text "Register" ]
                    ]
                ]
            ]
        , div [ class "w-full text-center" ]
            [ text "Have an account?"
            , authLink Login "ml-6" [ text "Sign in" ]
            ]
        ]
